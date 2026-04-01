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
        $rows = $this->db->resultSet();
        foreach ($rows as &$row) {
            $row['items'] = $this->getItems((int)$row['id']);
        }
        return $rows;
    }

    public function getItems(int $purchaseId): array
    {
        $this->db->query("
            SELECT gpi.*, g.grocery_name, g.unit
            FROM grocery_purchase_items gpi
            LEFT JOIN groceries g ON g.id = gpi.grocery_id
            WHERE gpi.purchase_id = :purchase_id
            ORDER BY gpi.id ASC
        ");
        $this->db->bind(':purchase_id', $purchaseId);
        return $this->db->resultSet();
    }

    public function createFromPO(array $po, array $poItems, ?int $createdBy): int|false
    {
        $purchaseNo = 'GP-' . date('Ymd-His') . '-' . rand(100, 999);
        $totalAmount = 0;
        foreach ($poItems as $item) {
            $totalAmount += (float)$item['line_total'];
        }

        $this->db->query("
            INSERT INTO grocery_purchases (
                purchase_no, supplier_id, source_po_id, purchase_date, due_date, status, total_amount,
                amount_paid, balance_due, payment_status, expense_posted, notes, created_by, created_at
            ) VALUES (
                :purchase_no, :supplier_id, :source_po_id, CURDATE(), NULL, 'draft', :total_amount,
                0, :balance_due, 'unpaid', 0, :notes, :created_by, NOW()
            )
        ");
        $this->db->bind(':purchase_no', $purchaseNo);
        $this->db->bind(':supplier_id', $po['supplier_id']);
        $this->db->bind(':source_po_id', $po['id']);
        $this->db->bind(':total_amount', $totalAmount);
        $this->db->bind(':balance_due', $totalAmount);
        $this->db->bind(':notes', 'Generated from PO ' . ($po['po_no'] ?? ''));
        $this->db->bind(':created_by', $createdBy);

        if (!$this->db->execute()) return false;
        $purchaseId = (int)$this->db->lastInsertId();

        foreach ($poItems as $item) {
            $orderedQty = (float)($item['ordered_qty'] ?? 0);
            $unitCost = (float)($item['unit_cost'] ?? 0);
            $this->db->query("
                INSERT INTO grocery_purchase_items (
                    purchase_id, grocery_id, package_count, package_quantity, package_cost, total_quantity, line_total
                ) VALUES (
                    :purchase_id, :grocery_id, 1, :package_quantity, :package_cost, :total_quantity, :line_total
                )
            ");
            $this->db->bind(':purchase_id', $purchaseId);
            $this->db->bind(':grocery_id', $item['grocery_id']);
            $this->db->bind(':package_quantity', $orderedQty);
            $this->db->bind(':package_cost', $unitCost * $orderedQty);
            $this->db->bind(':total_quantity', $orderedQty);
            $this->db->bind(':line_total', $unitCost * $orderedQty);
            if (!$this->db->execute()) return false;
        }

        return $purchaseId;
    }
}
