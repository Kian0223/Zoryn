<?php
class SupplierPurchaseOrdersController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $poModel = $this->model('SupplierPurchaseOrder');
        $planModel = $this->model('PurchasePlan');

        $this->view('supplier_purchase_orders/index', [
            'title' => 'Supplier Purchase Orders',
            'summary' => $poModel->getSummary(),
            'pos' => $poModel->getAll(),
            'plans' => $planModel->getAll(),
        ]);
    }

    public function createFromPlan($planId): void
    {
        $this->requireLogin();

        $planModel = $this->model('PurchasePlan');
        $poModel = $this->model('SupplierPurchaseOrder');

        $plan = $planModel->findById((int)$planId);
        if (!$plan || ($plan['status'] ?? '') !== 'approved') {
            $_SESSION['error'] = 'Only approved plans can be converted to purchase orders.';
            $this->redirect('supplierpurchaseorders/index');
            return;
        }

        $itemsBySupplier = [];
        foreach (($plan['items'] ?? []) as $item) {
            $supplierId = (int)($item['supplier_id'] ?? 0);
            if ($supplierId <= 0) continue;
            $itemsBySupplier[$supplierId][] = $item;
        }

        if (empty($itemsBySupplier)) {
            $_SESSION['error'] = 'No supplier assigned to approved plan items.';
            $this->redirect('supplierpurchaseorders/index');
            return;
        }

        $created = 0;
        foreach ($itemsBySupplier as $supplierId => $items) {
            $poId = $poModel->createFromPlanBySupplier((int)$planId, $supplierId, $items, (int)($_SESSION['user']['id'] ?? 0), null, 'Generated from approved plan');
            if ($poId) $created++;
        }

        if ($created > 0) {
            $planModel->updateStatus((int)$planId, 'converted', (int)($_SESSION['user']['id'] ?? 0));
            $_SESSION['success'] = $created . ' supplier purchase order(s) created from approved plan.';
        } else {
            $_SESSION['error'] = 'Failed to create supplier purchase orders.';
        }

        $this->redirect('supplierpurchaseorders/index');
    }

    public function issue($poId): void
    {
        $this->requireLogin();
        $poModel = $this->model('SupplierPurchaseOrder');
        $poModel->updateStatus((int)$poId, 'issued');
        $_SESSION['success'] = 'Purchase order issued.';
        $this->redirect('supplierpurchaseorders/index');
    }

    public function print($poId): void
    {
        $this->requireLogin();
        $poModel = $this->model('SupplierPurchaseOrder');
        $po = $poModel->findById((int)$poId);

        if (!$po) {
            $_SESSION['error'] = 'Purchase order not found.';
            $this->redirect('supplierpurchaseorders/index');
            return;
        }

        $this->view('supplier_purchase_orders/print', [
            'title' => 'Print Purchase Order',
            'po' => $po,
        ]);
    }

    public function createReceivingFromPO($poId): void
    {
        $this->requireLogin();

        $poModel = $this->model('SupplierPurchaseOrder');
        $purchaseModel = $this->model('GroceryPurchase');

        $po = $poModel->findById((int)$poId);
        if (!$po) {
            $_SESSION['error'] = 'Purchase order not found.';
            $this->redirect('supplierpurchaseorders/index');
            return;
        }

        $purchaseId = $purchaseModel->createFromPO($po, $po['items'] ?? [], (int)($_SESSION['user']['id'] ?? 0));
        if ($purchaseId) {
            $poModel->updateStatus((int)$poId, 'partially_received');
            $_SESSION['success'] = 'Receiving draft created from purchase order.';
        } else {
            $_SESSION['error'] = 'Failed to create receiving draft from PO.';
        }

        $this->redirect('grocerypurchases/index');
    }
}
