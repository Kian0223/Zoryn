<?php
class SupplierReturnsController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $returnModel = $this->model('SupplierReturn');
        $creditModel = $this->model('SupplierCreditMemo');
        $poModel = $this->model('SupplierPurchaseOrder');

        $this->view('supplier_returns/index', [
            'title' => 'Supplier Returns',
            'summary' => $returnModel->getSummary(),
            'credit_summary' => $creditModel->getSummary(),
            'returns' => $returnModel->getAll(),
            'credit_memos' => $creditModel->getAll(),
            'open_balances' => $poModel->getOpenBalances(),
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('supplierreturns/index');
            return;
        }

        $qty = (float)($_POST['quantity'] ?? 0);
        $unitCost = (float)($_POST['unit_cost'] ?? 0);
        $returnModel = $this->model('SupplierReturn');

        $returnId = $returnModel->create([
            'po_id' => (int)($_POST['po_id'] ?? 0),
            'po_item_id' => (int)($_POST['po_item_id'] ?? 0),
            'supplier_id' => (int)($_POST['supplier_id'] ?? 0),
            'grocery_id' => (int)($_POST['grocery_id'] ?? 0),
            'return_date' => trim($_POST['return_date'] ?? date('Y-m-d')),
            'return_type' => trim($_POST['return_type'] ?? 'damaged'),
            'quantity' => $qty,
            'unit_cost' => $unitCost,
            'line_total' => $qty * $unitCost,
            'notes' => trim($_POST['notes'] ?? ''),
            'created_by' => (int)($_SESSION['user']['id'] ?? 0),
        ]);

        $_SESSION['success'] = $returnId ? 'Supplier return recorded.' : 'Failed to save supplier return.';
        $this->redirect('supplierreturns/index');
    }

    public function approve($id): void
    {
        $this->requireLogin();
        $returnModel = $this->model('SupplierReturn');
        $returnModel->updateStatus((int)$id, 'approved');
        $_SESSION['success'] = 'Supplier return approved.';
        $this->redirect('supplierreturns/index');
    }

    public function credit($id): void
    {
        $this->requireLogin();

        $returnModel = $this->model('SupplierReturn');
        $creditModel = $this->model('SupplierCreditMemo');

        $return = $returnModel->findById((int)$id);
        if (!$return) {
            $_SESSION['error'] = 'Return not found.';
            $this->redirect('supplierreturns/index');
            return;
        }

        $memoId = $creditModel->create([
            'supplier_id' => (int)$return['supplier_id'],
            'return_id' => (int)$return['id'],
            'purchase_id' => null,
            'memo_date' => date('Y-m-d'),
            'amount' => (float)$return['line_total'],
            'notes' => 'Credit memo from return ' . ($return['return_no'] ?? ''),
            'created_by' => (int)($_SESSION['user']['id'] ?? 0),
        ]);

        if ($memoId) {
            $returnModel->updateStatus((int)$id, 'credited');
            $_SESSION['success'] = 'Credit memo created from supplier return.';
        } else {
            $_SESSION['error'] = 'Failed to create credit memo.';
        }

        $this->redirect('supplierreturns/index');
    }

    public function applyCredit(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('supplierreturns/index');
            return;
        }

        $memoId = (int)($_POST['memo_id'] ?? 0);
        $purchaseId = (int)($_POST['purchase_id'] ?? 0);
        $applyAmount = (float)($_POST['apply_amount'] ?? 0);

        $creditModel = $this->model('SupplierCreditMemo');
        $purchaseModel = $this->model('GroceryPurchase');

        $ok1 = $creditModel->applyToPurchase($memoId, $purchaseId, $applyAmount);
        $ok2 = $ok1 ? $purchaseModel->applySupplierCredit($purchaseId, $applyAmount) : false;

        $_SESSION['success'] = ($ok1 && $ok2) ? 'Credit memo applied to purchase.' : 'Failed to apply credit memo.';
        $this->redirect('supplierreturns/index');
    }
}
