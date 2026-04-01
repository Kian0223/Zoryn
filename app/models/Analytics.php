<?php
class Analytics extends Model
{
    public function getMonthlySalesExpensesProfit(int $months = 12): array
    {
        $months = max(1, (int)$months);

        $this->db->query("
            SELECT
                month_key,
                SUM(sales_total) AS sales_total,
                SUM(expense_total) AS expense_total,
                SUM(sales_total) - SUM(expense_total) AS profit_total
            FROM (
                SELECT DATE_FORMAT(sale_date, '%Y-%m') AS month_key,
                       total_amount AS sales_total,
                       0 AS expense_total
                FROM sales
                WHERE sale_status = 'completed'
                  AND sale_date >= DATE_SUB(CURDATE(), INTERVAL {$months} MONTH)

                UNION ALL

                SELECT DATE_FORMAT(expense_date, '%Y-%m') AS month_key,
                       0 AS sales_total,
                       amount AS expense_total
                FROM expenses
                WHERE expense_date >= DATE_SUB(CURDATE(), INTERVAL {$months} MONTH)
            ) t
            GROUP BY month_key
            ORDER BY month_key ASC
        ");

        $rows = $this->db->resultSet();
        return is_array($rows) ? $rows : [];
    }

    public function getDashboardTotals(): array
    {
        $this->db->query("
            SELECT
                (SELECT COALESCE(SUM(total_amount), 0) FROM sales WHERE sale_status = 'completed') AS total_sales,
                (SELECT COALESCE(SUM(amount), 0) FROM expenses) AS total_expenses,
                (SELECT COUNT(*) FROM orders) AS total_orders,
                (SELECT COUNT(*) FROM grocery_purchases WHERE status = 'received') AS total_received_purchases
        ");

        $row = $this->db->single();

        return [
            'total_sales' => (float)($row->total_sales ?? 0),
            'total_expenses' => (float)($row->total_expenses ?? 0),
            'total_profit' => (float)($row->total_sales ?? 0) - (float)($row->total_expenses ?? 0),
            'total_orders' => (int)($row->total_orders ?? 0),
            'total_received_purchases' => (int)($row->total_received_purchases ?? 0),
        ];
    }

    public function getTopSuppliers(int $limit = 10): array
    {
        $limit = max(1, (int)$limit);

        $this->db->query("
            SELECT
                s.supplier_name,
                COALESCE(SUM(gp.total_amount), 0) AS total_purchased,
                COUNT(gp.id) AS purchase_count
            FROM suppliers s
            LEFT JOIN grocery_purchases gp ON gp.supplier_id = s.id AND gp.status = 'received'
            GROUP BY s.id, s.supplier_name
            ORDER BY total_purchased DESC, purchase_count DESC
            LIMIT {$limit}
        ");

        $rows = $this->db->resultSet();
        return is_array($rows) ? $rows : [];
    }

    public function getTopExpenseCategories(int $limit = 10): array
    {
        $limit = max(1, (int)$limit);

        $this->db->query("
            SELECT
                COALESCE(category, 'Uncategorized') AS category_name,
                COALESCE(SUM(amount), 0) AS total_amount,
                COUNT(*) AS entry_count
            FROM expenses
            GROUP BY COALESCE(category, 'Uncategorized')
            ORDER BY total_amount DESC, entry_count DESC
            LIMIT {$limit}
        ");

        $rows = $this->db->resultSet();
        return is_array($rows) ? $rows : [];
    }

    public function getSalesByPaymentMethod(): array
    {
        $this->db->query("
            SELECT
                payment_method,
                COALESCE(SUM(total_amount), 0) AS total_amount,
                COUNT(*) AS receipt_count
            FROM sales
            WHERE sale_status = 'completed'
            GROUP BY payment_method
            ORDER BY total_amount DESC
        ");

        $rows = $this->db->resultSet();
        return is_array($rows) ? $rows : [];
    }

    public function getMonthlySnapshot(): array
    {
        $this->db->query("
            SELECT
                (SELECT COALESCE(SUM(total_amount),0) FROM sales WHERE sale_status='completed' AND DATE_FORMAT(sale_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')) AS current_month_sales,
                (SELECT COALESCE(SUM(amount),0) FROM expenses WHERE DATE_FORMAT(expense_date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')) AS current_month_expenses
        ");

        $row = $this->db->single();

        return [
            'current_month_sales' => (float)($row->current_month_sales ?? 0),
            'current_month_expenses' => (float)($row->current_month_expenses ?? 0),
            'current_month_profit' => (float)($row->current_month_sales ?? 0) - (float)($row->current_month_expenses ?? 0),
        ];
    }
}