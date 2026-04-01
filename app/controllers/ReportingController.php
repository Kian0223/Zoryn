<?php
class ReportingController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $reportingModel = $this->model('Reporting');

        $this->view('reporting/index', [
            'title' => 'Performance Reports',
            'menu_engineering' => $reportingModel->getMenuEngineering(),
            'best_viands' => $reportingModel->getBestSellingViands(15),
            'best_products' => $reportingModel->getBestSellingProducts(15),
            'slow_groceries' => $reportingModel->getSlowMovingGroceries(20),
            'slow_products' => $reportingModel->getSlowMovingProducts(20),
        ]);
    }
}
