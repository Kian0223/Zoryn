<?php
class Product extends Model
{
    public function getAll(): array
    {
        $this->db->query("SELECT p.*, c.category_name
                         FROM products p
                         LEFT JOIN categories c ON c.id = p.category_id
                         ORDER BY p.product_name ASC");
        return $this->db->resultSet();
    }

    public function getTotalProducts(): int
    {
        $this->db->query("SELECT COUNT(*) AS total FROM products");
        $row = $this->db->single();
        return (int)($row['total'] ?? 0);
    }

    public function getLowStockCount(): int
    {
        $this->db->query("
            SELECT COUNT(*) AS total
            FROM products
            WHERE current_stock <= low_stock_threshold
        ");
        $row = $this->db->single();
        return (int)($row['total'] ?? 0);
    }

    public function getInventoryItems(): array
    {
        $this->db->query("
            SELECT 
                p.id,
                'product' AS item_type,
                p.product_name AS item_name,
                COALESCE(c.category_name, 'Products') AS category_name,
                p.current_stock,
                p.low_stock_threshold,
                p.unit,
                p.cost_price,
                p.supplier_name,
                CASE
                    WHEN p.current_stock <= 0 THEN 'out'
                    WHEN p.current_stock <= p.low_stock_threshold THEN 'low'
                    ELSE 'ok'
                END AS stock_status
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            ORDER BY p.product_name ASC
        ");
        return $this->db->resultSet();
    }

    public function create(array $data): bool
    {
        $this->db->query("INSERT INTO products (
                category_id, product_name, sku, unit, selling_price, cost_price, current_stock, low_stock_threshold, supplier_name
            ) VALUES (
                :category_id, :product_name, :sku, :unit, :selling_price, :cost_price, :current_stock, :low_stock_threshold, :supplier_name
            )");
        $this->db->bind(':category_id', $data['category_id'] ?: null);
        $this->db->bind(':product_name', $data['product_name']);
        $this->db->bind(':sku', $data['sku']);
        $this->db->bind(':unit', $data['unit']);
        $this->db->bind(':selling_price', $data['selling_price']);
        $this->db->bind(':cost_price', $data['cost_price'] ?? 0);
        $this->db->bind(':current_stock', $data['current_stock']);
        $this->db->bind(':low_stock_threshold', $data['low_stock_threshold'] ?? 5);
        $this->db->bind(':supplier_name', $data['supplier_name'] ?? null);
        return $this->db->execute();
    }

    public function findById(int $id): array|false
    {
        $this->db->query("SELECT * FROM products WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function update(int $id, array $data): bool
    {
        $this->db->query("UPDATE products
                         SET category_id = :category_id,
                             product_name = :product_name,
                             sku = :sku,
                             unit = :unit,
                             selling_price = :selling_price,
                             cost_price = :cost_price,
                             current_stock = :current_stock,
                             low_stock_threshold = :low_stock_threshold,
                             supplier_name = :supplier_name
                         WHERE id = :id");
        $this->db->bind(':category_id', $data['category_id'] ?: null);
        $this->db->bind(':product_name', $data['product_name']);
        $this->db->bind(':sku', $data['sku']);
        $this->db->bind(':unit', $data['unit']);
        $this->db->bind(':selling_price', $data['selling_price']);
        $this->db->bind(':cost_price', $data['cost_price'] ?? 0);
        $this->db->bind(':current_stock', $data['current_stock']);
        $this->db->bind(':low_stock_threshold', $data['low_stock_threshold'] ?? 5);
        $this->db->bind(':supplier_name', $data['supplier_name'] ?? null);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function adjustStock(int $id, float $quantity): bool
    {
        $this->db->query("UPDATE products SET current_stock = current_stock + :quantity WHERE id = :id");
        $this->db->bind(':quantity', $quantity);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getLatestLoggedCost(int $productId): float
    {
        $this->db->query("
            SELECT unit_cost
            FROM stock_movements
            WHERE item_type = 'product' AND item_id = :item_id AND unit_cost > 0
            ORDER BY movement_date DESC, id DESC
            LIMIT 1
        ");
        $this->db->bind(':item_id', $productId);
        $row = $this->db->single();
        return (float)($row['unit_cost'] ?? 0);
    }

    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM products WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
