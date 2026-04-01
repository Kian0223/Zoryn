<?php
class SupplierPaymentsController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $paymentModel = $this->model('SupplierPayment');
        $supplierModel = $this->model('Supplier');
        $purchaseModel = $this->model('GroceryPurchase');

        $this->view('supplier_payments/index', [
            'title' => 'Supplier Payments',
            'payments' => $paymentModel->getAll(),
            'suppliers' => $supplierModel->getAll(),
            'purchases' => $purchaseModel->getAll(),
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('supplierpayments/index');
            return;
        }

        $supplierId = (int)($_POST['supplier_id'] ?? 0);
        $purchaseId = (int)($_POST['purchase_id'] ?? 0);
        $paymentDate = trim($_POST['payment_date'] ?? date('Y-m-d'));
        $amount = (float)($_POST['amount'] ?? 0);
        $paymentMethod = trim($_POST['payment_method'] ?? 'cash');
        $referenceNo = trim($_POST['reference_no'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if ($supplierId <= 0 || $amount <= 0) {
            $_SESSION['error'] = 'Supplier and payment amount are required.';
            $this->redirect('supplierpayments/index');
            return;
        }

        $paymentModel = $this->model('SupplierPayment');
        $purchaseModel = $this->model('GroceryPurchase');

        $ok = $paymentModel->create([
            'supplier_id' => $supplierId,
            'purchase_id' => $purchaseId > 0 ? $purchaseId : null,
            'payment_date' => $paymentDate,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'reference_no' => $referenceNo,
            'notes' => $notes,
            'created_by' => $_SESSION['user']['id'] ?? null,
        ]);

        if ($ok && $purchaseId > 0) {
            $purchaseModel->applyPayment($purchaseId, $amount);
        }

        $_SESSION['success'] = $ok ? 'Supplier payment recorded successfully.' : 'Failed to save payment.';
        $this->redirect('supplierpayments/index');
    }
}
