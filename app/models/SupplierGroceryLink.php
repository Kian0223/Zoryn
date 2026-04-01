<?php
class SupplierGroceryLink extends Model
{
    public function getAll(): array
    {
        $this->db->query("
            SELECT
                sgl.*,
                s.supplier_name,
                g.grocery_name,
                g.unit
            FROM supplier_grocery_links sgl
            INNER JOIN suppliers s ON s.id = sgl.supplier_id
            INNER JOIN groceries g ON g.id = sgl.grocery_id
            ORDER BY s.supplier_name ASC, g.grocery_name ASC
        ");
        return $this->db->resultSet();
    }

    public function createOrUpdate(array $data): bool
    {
        $this->db->query("
            INSERT INTO supplier_grocery_links (
                supplier_id, grocery_id, lead_time_days, preferred_flag, last_cost, notes, created_at
            ) VALUES (
                :supplier_id, :grocery_id, :lead_time_days, :preferred_flag, :last_cost, :notes, NOW()
            )
            ON DUPLICATE KEY UPDATE
                lead_time_days = VALUES(lead_time_days),
                preferred_flag = VALUES(preferred_flag),
                last_cost = VALUES(last_cost),
                notes = VALUES(notes)
        ");
        $this->db->bind(':supplier_id', $data['supplier_id']);
        $this->db->bind(':grocery_id', $data['grocery_id']);
        $this->db->bind(':lead_time_days', $data['lead_time_days']);
        $this->db->bind(':preferred_flag', !empty($data['preferred_flag']) ? 1 : 0);
        $this->db->bind(':last_cost', $data['last_cost']);
        $this->db->bind(':notes', $data['notes'] ?: null);
        return $this->db->execute();
    }
}
