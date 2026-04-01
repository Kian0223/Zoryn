<?php
class SupplierReturn extends Model
{
    public function getAll(): array
    {
        $this->db->query("
            SELECT sr.*, spo.po_no, s.supplier_name, g.grocery_name, g.unit, u.full_name AS creator_name
            FROM supplier_returns sr
            LEFT JOIN supplier_purchase_orders spo ON spo.id = sr.po_id
            INNER JOIN suppliers s ON s.id = sr.supplier_id
            INNER JOIN groceries g ON g.id = sr.grocery_id
            LEFT JOIN users u ON u.id = sr.created_by
            ORDER BY sr.id DESC
        ");
        return $this->db->resultSet();
    }

    public function create(array $data): int|false
    {
        $returnNo = 'RET-' . date('Ymd-His') . '-' . rand(100, 999);

        $this->db->query("
            INSERT INTO supplier_returns (
                return_no, po_id, po_item_id, supplier_id, grocery_id, return_date,
                return_type, quantity, unit_cost, line_total, status, notes, created_by, created_at
            ) VALUES (
                :return_no, :po_id, :po_item_id, :supplier_id, :grocery_id, :return_date,
                :return_type, :quantity, :unit_cost, :line_total, 'pending', :notes, :created_by, NOW()
            )
        ");
        $this->db->bind(':return_no', $returnNo);
        $this->db->bind(':po_id', $data['po_id'] ?: null);
        $this->db->bind(':po_item_id', $data['po_item_id'] ?: null);
        $this->db->bind(':supplier_id', $data['supplier_id']);
        $this->db->bind(':grocery_id', $data['grocery_id']);
        $this->db->bind(':return_date', $data['return_date']);
        $this->db->bind(':return_type', $data['return_type']);
        $this->db->bind(':quantity', $data['quantity']);
        $this->db->bind(':unit_cost', $data['unit_cost']);
        $this->db->bind(':line_total', $data['line_total']);
        $this->db->bind(':notes', $data['notes'] ?: null);
        $this->db->bind(':created_by', $data['created_by'] ?? null);

        if (!$this->db->execute()) return false;
        return (int)$this->db->lastInsertId();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $this->db->query("UPDATE supplier_returns SET status = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function findById(int $id): array|false
    {
        $this->db->query("SELECT * FROM supplier_returns WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getSummary(): array
    {
        $this->db->query("
            SELECT
                COUNT(*) AS total_returns,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_returns,
                SUM(CASE WHEN status = 'credited' THEN 1 ELSE 0 END) AS credited_returns,
                COALESCE(SUM(line_total), 0) AS total_return_value
            FROM supplier_returns
        ");
        return $this->db->single() ?: [
            'total_returns' => 0,
            'pending_returns' => 0,
            'credited_returns' => 0,
            'total_return_value' => 0,
        ];
    }
}
