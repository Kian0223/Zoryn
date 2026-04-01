<?php
class GroceryPurchasesController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $purchaseModel = $this->model('GroceryPurchase');
        $supplierModel = $this->model('Supplier');
        $groceryModel = $this->model('Grocery');
        $termModel = $this->model('APTerm');

        $this->view('grocery_purchases/index', [
            'title' => 'Grocery Receiving',
            'purchases' => $purchaseModel->getAll(),
            'summary' => $purchaseModel->getSummary(),
            'aging' => $purchaseModel->getAgingBuckets(),
            'suppliers' => $supplierModel->getAll(),
            'groceries' => $groceryModel->getAll(),
            'default_days_due' => $termModel->getDefaultDays(),
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('grocerypurchases/index');
            return;
        }

        $supplierId = (int)($_POST['supplier_id'] ?? 0);
        $purchaseDate = trim($_POST['purchase_date'] ?? date('Y-m-d'));
        $dueDate = trim($_POST['due_date'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        $groceryIds = $_POST['grocery_id'] ?? [];
        $packageCounts = $_POST['package_count'] ?? [];
        $packageQuantities = $_POST['package_quantity'] ?? [];
        $packageCosts = $_POST['package_cost'] ?? [];

        $items = [];
        foreach ($groceryIds as $i => $groceryId) {
            $groceryId = (int)$groceryId;
            $packageCount = (float)($packageCounts[$i] ?? 0);
            $packageQuantity = (float)($packageQuantities[$i] ?? 0);
            $packageCost = (float)($packageCosts[$i] ?? 0);

            if ($groceryId <= 0 || $packageCount <= 0 || $packageQuantity <= 0) {
                continue;
            }

            $totalQuantity = $packageCount * $packageQuantity;
            $lineTotal = $packageCount * $packageCost;

            $items[] = [
                'grocery_id' => $groceryId,
                'package_count' => $packageCount,
                'package_quantity' => $packageQuantity,
                'package_cost' => $packageCost,
                'total_quantity' => $totalQuantity,
                'line_total' => $lineTotal,
            ];
        }

        if (empty($items)) {
            $_SESSION['error'] = 'Please add at least one grocery item.';
            $this->redirect('grocerypurchases/index');
            return;
        }

        $purchaseModel = $this->model('GroceryPurchase');
        $purchaseId = $purchaseModel->create([
            'supplier_id' => $supplierId > 0 ? $supplierId : null,
            'purchase_date' => $purchaseDate,
            'due_date' => $dueDate !== '' ? $dueDate : null,
            'status' => 'draft',
            'notes' => $notes,
            'created_by' => $_SESSION['user']['id'] ?? null,
        ], $items);

        $_SESSION['success'] = $purchaseId ? 'Grocery purchase saved as draft.' : 'Failed to save grocery purchase.';
        $this->redirect('grocerypurchases/index');
    }

    public function receive($id): void
    {
        $this->requireLogin();

        $purchaseModel = $this->model('GroceryPurchase');
        $groceryModel = $this->model('Grocery');
        $expenseModel = $this->model('Expense');

        $purchase = $purchaseModel->findById((int)$id);
        if (!$purchase) {
            $_SESSION['error'] = 'Purchase not found.';
            $this->redirect('grocerypurchases/index');
            return;
        }

        if (($purchase['status'] ?? '') !== 'draft') {
            $_SESSION['error'] = 'Only draft purchases can be received.';
            $this->redirect('grocerypurchases/index');
            return;
        }

        foreach (($purchase['items'] ?? []) as $item) {
            $groceryModel->receiveStock(
                (int)$item['grocery_id'],
                (float)$item['package_count'],
                (float)$item['package_quantity'],
                (float)$item['package_cost']
            );
        }

        $purchaseModel->markReceived((int)$id);

        if (empty($purchase['expense_posted'])) {
            $desc = 'Grocery Purchase ' . ($purchase['purchase_no'] ?? ('#' . $purchase['id']));
            if (!empty($purchase['supplier_name'])) {
                $desc .= ' - ' . $purchase['supplier_name'];
            }

            $expenseModel->create([
                'description' => $desc,
                'amount' => (float)($purchase['total_amount'] ?? 0),
                'expense_date' => $purchase['purchase_date'] ?? date('Y-m-d'),
                'category' => 'Supplies',
                'notes' => 'Auto-posted from grocery purchase',
                'created_by' => $_SESSION['user']['id'] ?? null,
            ]);

            $purchaseModel->markExpensePosted((int)$id);
        }

        $_SESSION['success'] = 'Purchase received, stock updated, and expense posted.';
        $this->redirect('grocerypurchases/index');
    }

    public function cancel($id): void
    {
        $this->requireLogin();
        $purchaseModel = $this->model('GroceryPurchase');
        $purchaseModel->markCancelled((int)$id);
        $_SESSION['success'] = 'Purchase cancelled.';
        $this->redirect('grocerypurchases/index');
    }
}
