<?php
class Stock extends Model
{
    public function getMovements(): array
    {
        $this->db->query("
            SELECT 
                sm.*,
                u.full_name,
                CASE 
                    WHEN sm.item_type = 'product' THEN p.product_name 
                    ELSE g.grocery_name 
                END AS item_name,
                CASE 
                    WHEN sm.item_type = 'product' THEN p.unit 
                    ELSE g.unit 
                END AS unit_name
            FROM stock_movements sm
            LEFT JOIN users u ON u.id = sm.created_by
            LEFT JOIN products p ON sm.item_type = 'product' AND sm.item_id = p.id
            LEFT JOIN groceries g ON sm.item_type = 'grocery' AND sm.item_id = g.id
            ORDER BY sm.movement_date DESC, sm.id DESC
        ");
        return $this->db->resultSet();
    }

    public function createMovement(array $data): bool
    {
        $this->db->query("
            INSERT INTO stock_movements (
                item_type,
                item_id,
                movement_type,
                quantity,
                package_count,
                package_quantity,
                package_cost,
                unit_cost,
                remarks,
                created_by
            ) VALUES (
                :item_type,
                :item_id,
                :movement_type,
                :quantity,
                :package_count,
                :package_quantity,
                :package_cost,
                :unit_cost,
                :remarks,
                :created_by
            )
        ");

        $this->db->bind(':item_type', $data['item_type']);
        $this->db->bind(':item_id', $data['item_id']);
        $this->db->bind(':movement_type', $data['movement_type']);
        $this->db->bind(':quantity', $data['quantity']);
        $this->db->bind(':package_count', $data['package_count'] ?? 0);
        $this->db->bind(':package_quantity', $data['package_quantity'] ?? 0);
        $this->db->bind(':package_cost', $data['package_cost'] ?? 0);
        $this->db->bind(':unit_cost', $data['unit_cost']);
        $this->db->bind(':remarks', $data['remarks']);
        $this->db->bind(':created_by', $data['created_by']);

        return $this->db->execute();
    }

    public function adjustProductStock(int $itemId, float $quantity): bool
    {
        $this->db->query("
            UPDATE products
            SET current_stock = current_stock + :quantity
            WHERE id = :id
        ");
        $this->db->bind(':quantity', $quantity);
        $this->db->bind(':id', $itemId);

        return $this->db->execute();
    }

    public function adjustGroceryStock(
        int $itemId,
        float $quantity,
        string $movementType,
        float $packageQuantity = 0,
        float $packageCost = 0
    ): bool {
        if ($movementType === 'stock_in' && $packageQuantity > 0) {
            $latestCost = $packageCost / $packageQuantity;

            $this->db->query("
                UPDATE groceries
                SET current_stock = current_stock + :quantity,
                    package_quantity = :package_quantity,
                    package_cost = :package_cost,
                    latest_cost = :latest_cost
                WHERE id = :id
            ");
            $this->db->bind(':quantity', $quantity);
            $this->db->bind(':package_quantity', $packageQuantity);
            $this->db->bind(':package_cost', $packageCost);
            $this->db->bind(':latest_cost', $latestCost);
            $this->db->bind(':id', $itemId);

            return $this->db->execute();
        }

        $this->db->query("
            UPDATE groceries
            SET current_stock = current_stock + :quantity
            WHERE id = :id
        ");
        $this->db->bind(':quantity', $quantity);
        $this->db->bind(':id', $itemId);

        return $this->db->execute();
    }
}