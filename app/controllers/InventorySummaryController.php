<?php
class InventorySummaryController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $groceryModel = $this->model('Grocery');

        $this->view('inventory_summary/index', [
            'title' => 'Inventory Summary',
            'items' => $groceryModel->getInventoryValuation(),
            'totals' => $groceryModel->getInventoryTotals(),
        ]);
    }
}
