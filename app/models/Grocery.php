<?php
class Grocery extends Model
{
    public function getAll(): array
    {
        $this->db->query("
            SELECT *,
                   CASE
                       WHEN current_stock <= 0 THEN 'out'
                       WHEN current_stock <= COALESCE(low_stock_threshold, 0) THEN 'low'
                       ELSE 'ok'
                   END AS stock_status
            FROM groceries
            ORDER BY grocery_name ASC
        ");
        return $this->db->resultSet();
    }

    public function getLowStockCount(): int
    {
        $this->db->query("
            SELECT COUNT(*) AS total
            FROM groceries
            WHERE current_stock > 0
              AND current_stock <= COALESCE(low_stock_threshold, 0)
        ");
        $row = $this->db->single();
        return (int)($row['total'] ?? 0);
    }

    public function getOutOfStockCount(): int
    {
        $this->db->query("
            SELECT COUNT(*) AS total
            FROM groceries
            WHERE current_stock <= 0
        ");
        $row = $this->db->single();
        return (int)($row['total'] ?? 0);
    }

    public function findById(int $id): array|false
    {
        $this->db->query("SELECT * FROM groceries WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function create(array $data): bool
    {
        $this->db->query("
            INSERT INTO groceries (
                grocery_name,
                unit,
                current_stock,
                package_quantity,
                package_cost,
                latest_cost,
                low_stock_threshold,
                reorder_point,
                reorder_quantity,
                safety_stock
            ) VALUES (
                :grocery_name,
                :unit,
                :current_stock,
                :package_quantity,
                :package_cost,
                :latest_cost,
                :low_stock_threshold,
                :reorder_point,
                :reorder_quantity,
                :safety_stock
            )
        ");

        $this->db->bind(':grocery_name', $data['grocery_name']);
        $this->db->bind(':unit', $data['unit'] ?? 'pcs');
        $this->db->bind(':current_stock', $data['current_stock'] ?? 0);
        $this->db->bind(':package_quantity', $data['package_quantity'] ?? 0);
        $this->db->bind(':package_cost', $data['package_cost'] ?? 0);
        $this->db->bind(':latest_cost', $data['latest_cost'] ?? 0);
        $this->db->bind(':low_stock_threshold', $data['low_stock_threshold'] ?? 10);
        $this->db->bind(':reorder_point', $data['reorder_point'] ?? 0);
        $this->db->bind(':reorder_quantity', $data['reorder_quantity'] ?? 0);
        $this->db->bind(':safety_stock', $data['safety_stock'] ?? 0);

        return $this->db->execute();
    }

    public function update(int $id, array $data): bool
    {
        $this->db->query("
            UPDATE groceries
            SET grocery_name = :grocery_name,
                unit = :unit,
                current_stock = :current_stock,
                package_quantity = :package_quantity,
                package_cost = :package_cost,
                latest_cost = :latest_cost,
                low_stock_threshold = :low_stock_threshold,
                reorder_point = :reorder_point,
                reorder_quantity = :reorder_quantity,
                safety_stock = :safety_stock
            WHERE id = :id
        ");

        $this->db->bind(':grocery_name', $data['grocery_name']);
        $this->db->bind(':unit', $data['unit'] ?? 'pcs');
        $this->db->bind(':current_stock', $data['current_stock'] ?? 0);
        $this->db->bind(':package_quantity', $data['package_quantity'] ?? 0);
        $this->db->bind(':package_cost', $data['package_cost'] ?? 0);
        $this->db->bind(':latest_cost', $data['latest_cost'] ?? 0);
        $this->db->bind(':low_stock_threshold', $data['low_stock_threshold'] ?? 10);
        $this->db->bind(':reorder_point', $data['reorder_point'] ?? 0);
        $this->db->bind(':reorder_quantity', $data['reorder_quantity'] ?? 0);
        $this->db->bind(':safety_stock', $data['safety_stock'] ?? 0);
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM groceries WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function adjustStock(int $id, float $quantity): bool
    {
        $this->db->query("
            UPDATE groceries
            SET current_stock = current_stock + :quantity
            WHERE id = :id
        ");
        $this->db->bind(':quantity', $quantity);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getInventoryItems(): array
    {
        $this->db->query("
            SELECT
                id,
                'grocery' AS item_type,
                grocery_name AS item_name,
                'Groceries' AS category_name,
                current_stock,
                COALESCE(low_stock_threshold, 0) AS low_stock_threshold,
                unit,
                latest_cost AS cost_price,
                NULL AS supplier_name,
                CASE
                    WHEN current_stock <= 0 THEN 'out'
                    WHEN current_stock <= COALESCE(low_stock_threshold, 0) THEN 'low'
                    ELSE 'ok'
                END AS stock_status
            FROM groceries
            ORDER BY grocery_name ASC
        ");
        return $this->db->resultSet();
    }

    public function getInventoryValuation(): array
    {
        $this->db->query("
            SELECT
                id,
                grocery_name,
                unit,
                current_stock,
                COALESCE(low_stock_threshold, 0) AS low_stock_threshold,
                COALESCE(package_quantity, 0) AS package_quantity,
                COALESCE(package_cost, 0) AS package_cost,
                COALESCE(latest_cost, 0) AS latest_cost,
                (current_stock * COALESCE(latest_cost, 0)) AS stock_value
            FROM groceries
            ORDER BY grocery_name ASC
        ");
        return $this->db->resultSet();
    }

    public function getInventoryTotals(): array
    {
        $this->db->query("
            SELECT
                COUNT(*) AS total_items,
                COALESCE(SUM(current_stock * COALESCE(latest_cost, 0)), 0) AS total_stock_value,
                SUM(CASE WHEN current_stock <= 0 THEN 1 ELSE 0 END) AS out_of_stock_count,
                SUM(CASE
                    WHEN current_stock > 0
                     AND current_stock <= COALESCE(low_stock_threshold, 0)
                    THEN 1 ELSE 0
                END) AS low_stock_count
            FROM groceries
        ");
        $row = $this->db->single();

        return [
            'total_items' => (int)($row['total_items'] ?? 0),
            'total_stock_value' => (float)($row['total_stock_value'] ?? 0),
            'out_of_stock_count' => (int)($row['out_of_stock_count'] ?? 0),
            'low_stock_count' => (int)($row['low_stock_count'] ?? 0),
        ];
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
        $this->db->bind(':reorder_point', $data['reorder_point'] ?? 0);
        $this->db->bind(':reorder_quantity', $data['reorder_quantity'] ?? 0);
        $this->db->bind(':safety_stock', $data['safety_stock'] ?? 0);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}