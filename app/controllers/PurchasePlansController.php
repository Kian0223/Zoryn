<?php
class PurchasePlansController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $planModel = $this->model('PurchasePlan');
        $forecastModel = $this->model('InventoryForecast');
        $quotationModel = $this->model('SupplierQuotation');
        $supplierModel = $this->model('Supplier');
        $groceryModel = $this->model('Grocery');

        $this->view('purchase_plans/index', [
            'title' => 'Purchase Planning',
            'summary' => $planModel->getSummary(),
            'plans' => $planModel->getAll(),
            'reorder_rows' => $forecastModel->getReorderSuggestions(30),
            'quotation_rows' => $quotationModel->getComparisonRows(),
            'suppliers' => $supplierModel->getAll(),
            'groceries' => $groceryModel->getAll(),
        ]);
    }

    public function createFromReorder(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('purchaseplans/index');
            return;
        }

        $forecastModel = $this->model('InventoryForecast');
        $planModel = $this->model('PurchasePlan');
        $rows = $forecastModel->getReorderSuggestions(30);

        $planId = $planModel->createFromSuggestions($rows, (int)($_SESSION['user']['id'] ?? 0), trim($_POST['notes'] ?? ''));
        $_SESSION['success'] = $planId ? 'Purchase plan created from reorder suggestions.' : 'Failed to create purchase plan.';
        $this->redirect('purchaseplans/index');
    }

    public function updateItem($itemId): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('purchaseplans/index');
            return;
        }

        $approvedQty = (float)($_POST['approved_qty'] ?? 0);
        $unitCost = (float)($_POST['unit_cost'] ?? 0);
        $supplierId = (int)($_POST['supplier_id'] ?? 0);

        $planModel = $this->model('PurchasePlan');
        $ok = $planModel->updateItem((int)$itemId, [
            'approved_qty' => $approvedQty,
            'unit_cost' => $unitCost,
            'estimated_total' => $approvedQty * $unitCost,
            'supplier_id' => $supplierId > 0 ? $supplierId : null,
        ]);

        $_SESSION['success'] = $ok ? 'Plan item updated.' : 'Failed to update item.';
        $this->redirect('purchaseplans/index');
    }

    public function submit($planId): void
    {
        $this->requireLogin();
        $planModel = $this->model('PurchasePlan');
        $planModel->updateStatus((int)$planId, 'submitted', null);
        $_SESSION['success'] = 'Purchase plan submitted.';
        $this->redirect('purchaseplans/index');
    }

    public function approve($planId): void
    {
        $this->requireLogin();
        $planModel = $this->model('PurchasePlan');
        $planModel->updateStatus((int)$planId, 'approved', (int)($_SESSION['user']['id'] ?? 0));
        $_SESSION['success'] = 'Purchase plan approved.';
        $this->redirect('purchaseplans/index');
    }

    public function reject($planId): void
    {
        $this->requireLogin();
        $planModel = $this->model('PurchasePlan');
        $planModel->updateStatus((int)$planId, 'rejected', (int)($_SESSION['user']['id'] ?? 0));
        $_SESSION['success'] = 'Purchase plan rejected.';
        $this->redirect('purchaseplans/index');
    }
}
