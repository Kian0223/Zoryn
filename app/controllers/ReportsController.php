<?php
class ReportsController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $dateFrom = trim($_GET['date_from'] ?? date('Y-m-01'));
        $dateTo = trim($_GET['date_to'] ?? date('Y-m-d'));

        $saleModel = $this->model('Sale');
        $expenseModel = $this->model('Expense');

        $summary = $saleModel->getRangeSummary($dateFrom, $dateTo);
        $sales = $saleModel->getSalesWithItems($dateFrom, $dateTo);
        $topItems = $saleModel->getTopSellingItems($dateFrom, $dateTo, 10);
        $revenueTrend = $saleModel->getRevenueTrend($dateFrom, $dateTo);
        $ordersByHour = $saleModel->getOrdersByHour($dateFrom, $dateTo);
        $expenseTotal = $expenseModel->getRangeTotal($dateFrom, $dateTo);

        $this->view('reports/index', [
            'title' => 'Reports & Analytics',
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'summary' => $summary,
            'sales' => $sales,
            'top_items' => $topItems,
            'revenue_trend' => $revenueTrend,
            'orders_by_hour' => $ordersByHour,
            'expense_total' => $expenseTotal,
            'net_profit' => (float)($summary['total_sales'] ?? 0) - $expenseTotal,
        ]);
    }
}
