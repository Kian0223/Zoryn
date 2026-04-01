<?php
class SupplierPayment extends Model
{
    public function getAll(): array
    {
        $this->db->query("
            SELECT sp.*, s.supplier_name, gp.purchase_no, u.full_name
            FROM supplier_payments sp
            LEFT JOIN suppliers s ON s.id = sp.supplier_id
            LEFT JOIN grocery_purchases gp ON gp.id = sp.purchase_id
            LEFT JOIN users u ON u.id = sp.created_by
            ORDER BY sp.payment_date DESC, sp.id DESC
        ");
        return $this->db->resultSet();
    }

    public function create(array $data): bool
    {
        $this->db->query("
            INSERT INTO supplier_payments (
                supplier_id, purchase_id, payment_date, amount, payment_method, reference_no, notes, created_by, created_at
            ) VALUES (
                :supplier_id, :purchase_id, :payment_date, :amount, :payment_method, :reference_no, :notes, :created_by, NOW()
            )
        ");
        $this->db->bind(':supplier_id', $data['supplier_id']);
        $this->db->bind(':purchase_id', $data['purchase_id'] ?: null);
        $this->db->bind(':payment_date', $data['payment_date']);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':payment_method', $data['payment_method']);
        $this->db->bind(':reference_no', $data['reference_no'] ?: null);
        $this->db->bind(':notes', $data['notes'] ?: null);
        $this->db->bind(':created_by', $data['created_by'] ?? null);
        return $this->db->execute();
    }

    public function getBySupplier(int $supplierId): array
    {
        $this->db->query("
            SELECT sp.*, gp.purchase_no
            FROM supplier_payments sp
            LEFT JOIN grocery_purchases gp ON gp.id = sp.purchase_id
            WHERE sp.supplier_id = :supplier_id
            ORDER BY sp.payment_date DESC, sp.id DESC
        ");
        $this->db->bind(':supplier_id', $supplierId);
        return $this->db->resultSet();
    }
}
