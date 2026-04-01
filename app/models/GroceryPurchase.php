<?php
class GroceryPurchase extends Model
{
    public function getAll(): array
    {
        $this->db->query("
            SELECT gp.*, s.supplier_name, u.full_name, spo.po_no,
                   CASE
                       WHEN gp.status <> 'received' THEN 'not_received'
                       WHEN gp.balance_due <= 0 THEN 'paid'
                       WHEN gp.due_date IS NULL THEN 'no_due_date'
                       WHEN gp.due_date < CURDATE() THEN 'overdue'
                       WHEN gp.due_date = CURDATE() THEN 'due_today'
                       ELSE 'upcoming'
                   END AS due_status
            FROM grocery_purchases gp
            LEFT JOIN suppliers s ON s.id = gp.supplier_id
            LEFT JOIN supplier_purchase_orders spo ON spo.id = gp.source_po_id
            LEFT JOIN users u ON u.id = gp.created_by
            ORDER BY gp.id DESC
        ");
        return $this->db->resultSet();
    }

    public function applySupplierCredit(int $purchaseId, float $amount): bool
    {
        $this->db->query("
            SELECT total_amount, amount_paid, supplier_credit_applied
            FROM grocery_purchases
            WHERE id = :id
            LIMIT 1
        ");
        $this->db->bind(':id', $purchaseId);
        $purchase = $this->db->single();
        if (!$purchase) return false;

        $applied = (float)($purchase['supplier_credit_applied'] ?? 0) + $amount;
        $paid = (float)($purchase['amount_paid'] ?? 0);
        $total = (float)($purchase['total_amount'] ?? 0);
        $balanceDue = max(0, $total - $paid - $applied);

        $paymentStatus = 'unpaid';
        if (($paid + $applied) > 0 && $balanceDue > 0) $paymentStatus = 'partial';
        if ($balanceDue <= 0) $paymentStatus = 'paid';

        $this->db->query("
            UPDATE grocery_purchases
            SET supplier_credit_applied = :supplier_credit_applied,
                balance_due = :balance_due,
                payment_status = :payment_status
            WHERE id = :id
        ");
        $this->db->bind(':supplier_credit_applied', $applied);
        $this->db->bind(':balance_due', $balanceDue);
        $this->db->bind(':payment_status', $paymentStatus);
        $this->db->bind(':id', $purchaseId);
        return $this->db->execute();
    }
}
