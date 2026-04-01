<?php
class Order extends Model
{
    public function getAllWithItems(): array
    {
        $this->db->query("
            SELECT
                o.*,
                dt.table_name,
                u.full_name AS cashier_name
            FROM orders o
            LEFT JOIN dining_tables dt ON dt.id = o.table_id
            LEFT JOIN users u ON u.id = o.created_by
            ORDER BY
                CASE o.status
                    WHEN 'pending' THEN 1
                    WHEN 'preparing' THEN 2
                    WHEN 'ready' THEN 3
                    WHEN 'served' THEN 4
                    WHEN 'completed' THEN 5
                    WHEN 'cancelled' THEN 6
                    ELSE 7
                END,
                o.created_at DESC,
                o.id DESC
        ");
        $orders = $this->db->resultSet();
        foreach ($orders as &$order) {
            $order['items'] = $this->getItems((int)$order['id']);
        }
        return $orders;
    }

    public function getKitchenQueue(): array
    {
        $this->db->query("
            SELECT o.*, dt.table_name
            FROM orders o
            LEFT JOIN dining_tables dt ON dt.id = o.table_id
            WHERE o.status IN ('pending','preparing','ready')
            ORDER BY
                CASE o.status
                    WHEN 'pending' THEN 1
                    WHEN 'preparing' THEN 2
                    WHEN 'ready' THEN 3
                    ELSE 4
                END,
                o.created_at ASC
        ");
        $orders = $this->db->resultSet();
        foreach ($orders as &$order) {
            $order['items'] = $this->getItems((int)$order['id']);
        }
        return $orders;
    }

    public function findById(int $id): array|false
    {
        $this->db->query("
            SELECT o.*, dt.table_name
            FROM orders o
            LEFT JOIN dining_tables dt ON dt.id = o.table_id
            WHERE o.id = :id
            LIMIT 1
        ");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function findByIdWithItems(int $id): array|false
    {
        $order = $this->findById($id);
        if (!$order) return false;
        $order['items'] = $this->getItems($id);
        return $order;
    }

    public function getItems(int $orderId): array
    {
        $this->db->query("
            SELECT oi.*,
                   CASE WHEN oi.viand_id IS NOT NULL THEN v.viand_name ELSE p.product_name END AS item_name,
                   CASE WHEN oi.viand_id IS NOT NULL THEN 'Viand' ELSE 'Product' END AS item_source
            FROM order_items oi
            LEFT JOIN viands v ON v.id = oi.viand_id
            LEFT JOIN products p ON p.id = oi.product_id
            WHERE oi.order_id = :order_id
            ORDER BY oi.id ASC
        ");
        $this->db->bind(':order_id', $orderId);
        return $this->db->resultSet();
    }

    public function getCounts(): array
    {
        $this->db->query("
            SELECT
                COUNT(*) AS total_orders,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_orders,
                SUM(CASE WHEN status = 'preparing' THEN 1 ELSE 0 END) AS preparing_orders,
                SUM(CASE WHEN status = 'ready' THEN 1 ELSE 0 END) AS ready_orders
            FROM orders
            WHERE DATE(created_at) = CURDATE()
        ");
        return $this->db->single() ?: [
            'total_orders' => 0,
            'pending_orders' => 0,
            'preparing_orders' => 0,
            'ready_orders' => 0,
        ];
    }

    public function createOrder(array $header, array $items): int|false
    {
        $orderNo = 'ORD-' . date('Ymd-His') . '-' . rand(100, 999);
        $totalAmount = 0;
        foreach ($items as $item) {
            $totalAmount += (float)$item['line_total'];
        }

        $this->db->query("
            INSERT INTO orders (
                order_no, order_type, status, table_id, customer_name, customer_phone, notes, total_amount, created_by, created_at
            ) VALUES (
                :order_no, :order_type, :status, :table_id, :customer_name, :customer_phone, :notes, :total_amount, :created_by, NOW()
            )
        ");
        $this->db->bind(':order_no', $orderNo);
        $this->db->bind(':order_type', $header['order_type']);
        $this->db->bind(':status', $header['status'] ?? 'pending');
        $this->db->bind(':table_id', $header['table_id'] ?: null);
        $this->db->bind(':customer_name', $header['customer_name'] ?: null);
        $this->db->bind(':customer_phone', $header['customer_phone'] ?: null);
        $this->db->bind(':notes', $header['notes'] ?: null);
        $this->db->bind(':total_amount', $totalAmount);
        $this->db->bind(':created_by', $header['created_by'] ?? null);

        if (!$this->db->execute()) return false;

        $orderId = (int)$this->db->lastInsertId();

        foreach ($items as $item) {
            $this->db->query("
                INSERT INTO order_items (order_id, viand_id, product_id, quantity, unit_price, line_total, notes)
                VALUES (:order_id, :viand_id, :product_id, :quantity, :unit_price, :line_total, :notes)
            ");
            $this->db->bind(':order_id', $orderId);
            $this->db->bind(':viand_id', $item['viand_id']);
            $this->db->bind(':product_id', $item['product_id']);
            $this->db->bind(':quantity', $item['quantity']);
            $this->db->bind(':unit_price', $item['unit_price']);
            $this->db->bind(':line_total', $item['line_total']);
            $this->db->bind(':notes', $item['notes'] ?? null);
            if (!$this->db->execute()) return false;
        }

        return $orderId;
    }

    public function updateStatus(int $id, string $status): bool
    {
        $allowed = ['pending', 'preparing', 'ready', 'served', 'completed', 'cancelled'];
        if (!in_array($status, $allowed, true)) return false;

        $this->db->query("UPDATE orders SET status = :status, updated_at = NOW() WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function attachSale(int $orderId, int $saleId): bool
    {
        $this->db->query("
            UPDATE orders
            SET sale_id = :sale_id,
                status = 'completed',
                updated_at = NOW()
            WHERE id = :id
        ");
        $this->db->bind(':sale_id', $saleId);
        $this->db->bind(':id', $orderId);
        return $this->db->execute();
    }
}
