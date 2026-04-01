<?php
class PurchasePlan extends Model
{
    public function getAll(): array
    {
        $this->db->query("
            SELECT pp.*, u.full_name AS creator_name
            FROM purchase_plans pp
            LEFT JOIN users u ON u.id = pp.created_by
            ORDER BY pp.id DESC
        ");
        $rows = $this->db->resultSet();
        foreach ($rows as &$row) {
            $row['items'] = $this->getItems((int)$row['id']);
        }
        return $rows;
    }

    public function getItems(int $planId): array
    {
        $this->db->query("
            SELECT ppi.*, g.grocery_name, g.unit, s.supplier_name
            FROM purchase_plan_items ppi
            INNER JOIN groceries g ON g.id = ppi.grocery_id
            LEFT JOIN suppliers s ON s.id = ppi.supplier_id
            WHERE ppi.plan_id = :plan_id
            ORDER BY g.grocery_name ASC
        ");
        $this->db->bind(':plan_id', $planId);
        return $this->db->resultSet();
    }

    public function findById(int $id): array|false
    {
        $this->db->query("SELECT * FROM purchase_plans WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        $row = $this->db->single();
        if (!$row) return false;
        $row['items'] = $this->getItems($id);
        return $row;
    }

    public function updateStatus(int $planId, string $status, ?int $approvedBy = null): bool
    {
        $this->db->query("
            UPDATE purchase_plans
            SET status = :status,
                approved_by = :approved_by,
                approved_at = CASE WHEN :approved_by IS NULL THEN approved_at ELSE NOW() END
            WHERE id = :id
        ");
        $this->db->bind(':status', $status);
        $this->db->bind(':approved_by', $approvedBy);
        $this->db->bind(':id', $planId);
        return $this->db->execute();
    }
}
