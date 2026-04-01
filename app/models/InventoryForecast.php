<?php
class InventoryForecast extends Model
{
    public function getUsageForecast(int $days = 30): array
    {
        $this->db->query("
            SELECT
                g.id,
                g.grocery_name,
                g.unit,
                g.current_stock,
                g.low_stock_threshold,
                g.reorder_point,
                g.reorder_quantity,
                g.safety_stock,
                g.latest_cost,
                COALESCE(usage.total_used, 0) AS total_used_last_30,
                COALESCE(usage.total_used / :days, 0) AS avg_daily_usage,
                CASE
                    WHEN COALESCE(usage.total_used, 0) <= 0 THEN NULL
                    ELSE ROUND(g.current_stock / (usage.total_used / :days), 2)
                END AS days_left_estimate,
                pref.supplier_id AS preferred_supplier_id,
                pref.supplier_name AS preferred_supplier_name
            FROM groceries g
            LEFT JOIN (
                SELECT
                    vi.grocery_id,
                    COALESCE(SUM(vi.quantity_needed * si.quantity), 0) AS total_used
                FROM viand_ingredients vi
                INNER JOIN sale_items si ON si.viand_id = vi.viand_id
                INNER JOIN sales s ON s.id = si.sale_id
                WHERE s.sale_status = 'completed'
                  AND DATE(s.sale_date) >= DATE_SUB(CURDATE(), INTERVAL :days2 DAY)
                GROUP BY vi.grocery_id
            ) usage ON usage.grocery_id = g.id
            LEFT JOIN (
                SELECT sgl.grocery_id, sgl.supplier_id, s.supplier_name
                FROM supplier_grocery_links sgl
                INNER JOIN suppliers s ON s.id = sgl.supplier_id
                WHERE sgl.preferred_flag = 1
            ) pref ON pref.grocery_id = g.id
            ORDER BY g.grocery_name ASC
        ");
        $this->db->bind(':days', $days);
        $this->db->bind(':days2', $days);
        return $this->db->resultSet();
    }

    public function getReorderSuggestions(int $days = 30): array
    {
        $rows = $this->getUsageForecast($days);
        $suggestions = [];

        foreach ($rows as $row) {
            $currentStock = (float)($row['current_stock'] ?? 0);
            $avgDailyUsage = (float)($row['avg_daily_usage'] ?? 0);
            $reorderPoint = (float)($row['reorder_point'] ?? 0);
            $reorderQty = (float)($row['reorder_quantity'] ?? 0);
            $safetyStock = (float)($row['safety_stock'] ?? 0);

            $effectiveReorderPoint = $reorderPoint > 0 ? $reorderPoint : max((float)($row['low_stock_threshold'] ?? 0), $avgDailyUsage * 3);
            $effectiveReorderQty = $reorderQty > 0 ? $reorderQty : max($avgDailyUsage * 7, 1);
            $effectiveTarget = $effectiveReorderPoint + $effectiveReorderQty + $safetyStock;

            $needsReorder = $currentStock <= $effectiveReorderPoint;
            $suggestedQty = $needsReorder ? max(0, $effectiveTarget - $currentStock) : 0;

            $row['effective_reorder_point'] = round($effectiveReorderPoint, 2);
            $row['effective_reorder_quantity'] = round($effectiveReorderQty, 2);
            $row['suggested_order_qty'] = round($suggestedQty, 2);
            $row['estimated_order_cost'] = round($suggestedQty * (float)($row['latest_cost'] ?? 0), 2);
            $row['needs_reorder'] = $needsReorder ? 1 : 0;

            if ($needsReorder) {
                $suggestions[] = $row;
            }
        }

        usort($suggestions, function($a, $b) {
            return ($a['days_left_estimate'] ?? 999999) <=> ($b['days_left_estimate'] ?? 999999);
        });

        return $suggestions;
    }

    public function getPlanningSummary(int $days = 30): array
    {
        $suggestions = $this->getReorderSuggestions($days);
        $totalItems = count($suggestions);
        $totalCost = 0;
        $urgentItems = 0;

        foreach ($suggestions as $row) {
            $totalCost += (float)($row['estimated_order_cost'] ?? 0);
            $daysLeft = $row['days_left_estimate'];
            if ($daysLeft !== null && (float)$daysLeft <= 3) $urgentItems++;
        }

        return [
            'items_to_reorder' => $totalItems,
            'urgent_items' => $urgentItems,
            'estimated_restock_cost' => round($totalCost, 2),
        ];
    }
}
