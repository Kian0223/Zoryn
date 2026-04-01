<?php
class AnalyticsController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $analyticsModel = $this->model('Analytics');

        $this->view('analytics/index', [
            'title' => 'Business Analytics',
            'totals' => $analyticsModel->getDashboardTotals(),
            'monthly' => $analyticsModel->getMonthlySalesExpensesProfit(12),
            'snapshot' => $analyticsModel->getMonthlySnapshot(),
            'top_suppliers' => $analyticsModel->getTopSuppliers(10),
            'top_expense_categories' => $analyticsModel->getTopExpenseCategories(10),
            'payment_methods' => $analyticsModel->getSalesByPaymentMethod(),
        ]);
    }
}
