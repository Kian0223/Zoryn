<?php
class Grocery extends Model
{
    public function getAll(): array
    {
        $this->db->query("
            SELECT *,
                   CASE
                       WHEN current_stock <= 0 THEN 'out'
                       WHEN current_stock <= low_stock_threshold THEN 'low'
                       ELSE 'ok'
                   END AS stock_status
            FROM groceries
            ORDER BY grocery_name ASC
        ");
        return $this->db->resultSet();
    }

    public function updatePlanning(int $id, array $data): bool
    {
        $this->db->query("
            UPDATE groceries
            SET reorder_point = :reorder_point,
                reorder_quantity = :reorder_quantity,
                safety_stock = :safety_stock
            WHERE id = :id
        ");
        $this->db->bind(':reorder_point', $data['reorder_point']);
        $this->db->bind(':reorder_quantity', $data['reorder_quantity']);
        $this->db->bind(':safety_stock', $data['safety_stock']);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
