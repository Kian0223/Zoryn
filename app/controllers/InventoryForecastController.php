<?php
class InventoryForecastController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $forecastModel = $this->model('InventoryForecast');
        $supplierLinkModel = $this->model('SupplierGroceryLink');
        $supplierModel = $this->model('Supplier');
        $groceryModel = $this->model('Grocery');

        $days = (int)($_GET['days'] ?? 30);
        if ($days <= 0) $days = 30;

        $this->view('inventory_forecast/index', [
            'title' => 'Inventory Forecast',
            'days' => $days,
            'summary' => $forecastModel->getPlanningSummary($days),
            'forecast_rows' => $forecastModel->getUsageForecast($days),
            'reorder_rows' => $forecastModel->getReorderSuggestions($days),
            'supplier_links' => $supplierLinkModel->getAll(),
            'suppliers' => $supplierModel->getAll(),
            'groceries' => $groceryModel->getAll(),
        ]);
    }

    public function updateGroceryPlanning($id): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('inventoryforecast/index');
            return;
        }

        $groceryModel = $this->model('Grocery');
        $ok = $groceryModel->updatePlanning((int)$id, [
            'reorder_point' => (float)($_POST['reorder_point'] ?? 0),
            'reorder_quantity' => (float)($_POST['reorder_quantity'] ?? 0),
            'safety_stock' => (float)($_POST['safety_stock'] ?? 0),
        ]);

        $_SESSION['success'] = $ok ? 'Grocery planning values updated.' : 'Failed to update planning values.';
        $this->redirect('inventoryforecast/index');
    }

    public function saveSupplierLink(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('inventoryforecast/index');
            return;
        }

        $linkModel = $this->model('SupplierGroceryLink');
        $ok = $linkModel->createOrUpdate([
            'supplier_id' => (int)($_POST['supplier_id'] ?? 0),
            'grocery_id' => (int)($_POST['grocery_id'] ?? 0),
            'lead_time_days' => (int)($_POST['lead_time_days'] ?? 3),
            'preferred_flag' => !empty($_POST['preferred_flag']) ? 1 : 0,
            'last_cost' => (float)($_POST['last_cost'] ?? 0),
            'notes' => trim($_POST['notes'] ?? ''),
        ]);

        $_SESSION['success'] = $ok ? 'Supplier planning link saved.' : 'Failed to save supplier planning link.';
        $this->redirect('inventoryforecast/index');
    }
}
