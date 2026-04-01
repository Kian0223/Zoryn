<?php
class Reporting extends Model
{
    public function getBestSellingViands(int $limit = 15): array
    {
        $limit = max(1, (int)$limit);

        $this->db->query("
            SELECT
                v.id,
                v.viand_name,
                v.selling_price,
                0 AS food_cost,
                COALESCE(SUM(si.quantity), 0) AS total_qty_sold,
                COALESCE(SUM(si.line_total), 0) AS total_sales
            FROM viands v
            LEFT JOIN sale_items si ON si.viand_id = v.id
            LEFT JOIN sales s ON s.id = si.sale_id
            WHERE s.sale_status = 'completed' OR s.sale_status IS NULL
            GROUP BY v.id, v.viand_name, v.selling_price
            ORDER BY total_qty_sold DESC, total_sales DESC
            LIMIT {$limit}
        ");

        $rows = $this->db->resultSet();
        return is_array($rows) ? $rows : [];
    }

    public function getBestSellingProducts(int $limit = 15): array
    {
        $limit = max(1, (int)$limit);

        $this->db->query("
            SELECT
                p.id,
                p.product_name,
                p.selling_price,
                COALESCE(SUM(si.quantity), 0) AS total_qty_sold,
                COALESCE(SUM(si.line_total), 0) AS total_sales
            FROM products p
            LEFT JOIN sale_items si ON si.product_id = p.id
            LEFT JOIN sales s ON s.id = si.sale_id
            WHERE s.sale_status = 'completed' OR s.sale_status IS NULL
            GROUP BY p.id, p.product_name, p.selling_price
            ORDER BY total_qty_sold DESC, total_sales DESC
            LIMIT {$limit}
        ");

        $rows = $this->db->resultSet();
        return is_array($rows) ? $rows : [];
    }

    public function getMenuEngineering(): array
    {
        $this->db->query("
            SELECT
                v.id,
                v.viand_name,
                v.selling_price,
                0 AS food_cost,
                v.selling_price AS estimated_profit,
                COALESCE(SUM(si.quantity), 0) AS qty_sold,
                COALESCE(SUM(si.line_total), 0) AS sales_total
            FROM viands v
            LEFT JOIN sale_items si ON si.viand_id = v.id
            LEFT JOIN sales s ON s.id = si.sale_id
            WHERE s.sale_status = 'completed' OR s.sale_status IS NULL
            GROUP BY v.id, v.viand_name, v.selling_price
            ORDER BY qty_sold DESC, estimated_profit DESC
        ");

        $rows = $this->db->resultSet();
        $rows = is_array($rows) ? $rows : [];

        $avgQty = 0;
        $avgProfit = 0;
        $count = count($rows);

        foreach ($rows as $row) {
            $avgQty += (float)($row['qty_sold'] ?? 0);
            $avgProfit += (float)($row['estimated_profit'] ?? 0);
        }

        if ($count > 0) {
            $avgQty /= $count;
            $avgProfit /= $count;
        }

        foreach ($rows as &$row) {
            $qty = (float)($row['qty_sold'] ?? 0);
            $profit = (float)($row['estimated_profit'] ?? 0);

            if ($qty >= $avgQty && $profit >= $avgProfit) {
                $row['menu_class'] = 'Star';
            } elseif ($qty >= $avgQty && $profit < $avgProfit) {
                $row['menu_class'] = 'Plowhorse';
            } elseif ($qty < $avgQty && $profit >= $avgProfit) {
                $row['menu_class'] = 'Puzzle';
            } else {
                $row['menu_class'] = 'Dog';
            }
        }
        unset($row);

        return $rows;
    }

    public function getSlowMovingGroceries(int $limit = 20): array
    {
        $limit = max(1, (int)$limit);

        $this->db->query("
            SELECT
                g.id,
                g.grocery_name,
                g.unit,
                g.current_stock,
                g.low_stock_threshold,
                g.latest_cost,
                COALESCE(used.total_used, 0) AS total_used,
                (g.current_stock * g.latest_cost) AS stock_value
            FROM groceries g
            LEFT JOIN (
                SELECT
                    vi.grocery_id,
                    COALESCE(SUM(vi.quantity_needed * si.quantity), 0) AS total_used
                FROM viand_ingredients vi
                INNER JOIN sale_items si ON si.viand_id = vi.viand_id
                INNER JOIN sales s ON s.id = si.sale_id AND s.sale_status = 'completed'
                GROUP BY vi.grocery_id
            ) used ON used.grocery_id = g.id
            ORDER BY total_used ASC, g.current_stock DESC, g.grocery_name ASC
            LIMIT {$limit}
        ");

        $rows = $this->db->resultSet();
        return is_array($rows) ? $rows : [];
    }

    public function getSlowMovingProducts(int $limit = 20): array
{
    $limit = max(1, (int)$limit);

    $this->db->query("
        SELECT
            p.id,
            p.product_name,
            p.selling_price,
            0 AS stock_quantity,
            COALESCE(SUM(si.quantity), 0) AS total_qty_sold,
            COALESCE(SUM(si.line_total), 0) AS total_sales
        FROM products p
        LEFT JOIN sale_items si ON si.product_id = p.id
        LEFT JOIN sales s ON s.id = si.sale_id
        WHERE s.sale_status = 'completed' OR s.sale_status IS NULL
        GROUP BY p.id, p.product_name, p.selling_price
        ORDER BY total_qty_sold ASC, p.product_name ASC
        LIMIT {$limit}
    ");

    $rows = $this->db->resultSet();
    return is_array($rows) ? $rows : [];
}
}