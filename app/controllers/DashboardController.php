<?php
class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $userModel = $this->model('User');
        $productModel = $this->model('Product');
        $viandModel = $this->model('Viand');
        $saleModel = $this->model('Sale');
        $groceryModel = $this->model('Grocery');
        $expenseModel = $this->model('Expense');

        $startDate = date('Y-m-01');
        $endDate = date('Y-m-d');

        $stats = [
            'total_users' => $userModel->getTotalUsers(),
            'total_products' => $productModel->getTotalProducts(),
            'total_viands' => $viandModel->getTotalViands(),
            'today_sales' => $saleModel->getTodaySalesAmount(),
            'total_receipts' => $saleModel->getTotalSalesCount(),
            'month_sales' => $saleModel->getRangeSummary($startDate, $endDate)['total_sales'],
            'month_expenses' => $expenseModel->getRangeTotal($startDate, $endDate),
            'product_low_stock' => $productModel->getLowStockCount(),
            'grocery_low_stock' => $groceryModel->getLowStockCount(),
        ];

        $recent_sales = $saleModel->getSalesWithItems(date('Y-m-d', strtotime('-7 days')), date('Y-m-d'));
        $top_items = $saleModel->getTopSellingItems($startDate, $endDate, 5);

        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'recent_sales' => array_slice($recent_sales, 0, 5),
            'top_items' => $top_items,
        ]);
    }
}
