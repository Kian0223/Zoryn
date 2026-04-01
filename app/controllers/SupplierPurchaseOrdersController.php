<?php
class SupplierPurchaseOrdersController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $poModel = $this->model('SupplierPurchaseOrder');
        $deliveryModel = $this->model('SupplierDeliveryLog');

        $this->view('supplier_purchase_orders/index', [
            'title' => 'Supplier Purchase Orders',
            'summary' => $poModel->getSummary(),
            'pos' => $poModel->getAll(),
            'open_balances' => $poModel->getOpenBalances(),
            'supplier_performance' => $deliveryModel->getSupplierPerformance(),
            'recent_deliveries' => $deliveryModel->getRecentLogs(),
        ]);
    }

    public function receiveLine($itemId): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('supplierpurchaseorders/index');
            return;
        }

        $poModel = $this->model('SupplierPurchaseOrder');
        $deliveryModel = $this->model('SupplierDeliveryLog');

        $poId = (int)($_POST['po_id'] ?? 0);
        $supplierId = (int)($_POST['supplier_id'] ?? 0);
        $groceryId = (int)($_POST['grocery_id'] ?? 0);
        $deliveredQty = (float)($_POST['delivered_qty'] ?? 0);
        $deliveryDate = trim($_POST['delivery_date'] ?? date('Y-m-d'));
        $expectedDate = trim($_POST['expected_date'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if ($deliveredQty <= 0) {
            $_SESSION['error'] = 'Delivered quantity must be greater than 0.';
            $this->redirect('supplierpurchaseorders/index');
            return;
        }

        $ok = $poModel->receiveLine((int)$itemId, $deliveredQty);

        if ($ok) {
            $leadDaysActual = null;
            if ($expectedDate !== '') {
                $d1 = new DateTime($deliveryDate);
                $d2 = new DateTime($expectedDate);
                $leadDaysActual = (int)$d2->diff($d1)->format('%r%a');
            }

            $onTime = 0;
            if ($expectedDate !== '' && $deliveryDate <= $expectedDate) {
                $onTime = 1;
            }

            $deliveryModel->create([
                'po_id' => $poId,
                'po_item_id' => (int)$itemId,
                'supplier_id' => $supplierId,
                'grocery_id' => $groceryId,
                'delivered_qty' => $deliveredQty,
                'delivery_date' => $deliveryDate,
                'expected_date' => $expectedDate !== '' ? $expectedDate : null,
                'lead_days_actual' => $leadDaysActual,
                'on_time_flag' => $onTime,
                'notes' => $notes,
            ]);

            $_SESSION['success'] = 'PO line received successfully.';
        } else {
            $_SESSION['error'] = 'Failed to receive PO line.';
        }

        $this->redirect('supplierpurchaseorders/index');
    }
}
