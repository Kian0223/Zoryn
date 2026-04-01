<?php
class ExpensesController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $expenseModel = $this->model('Expense');
        $supplierModel = $this->model('Supplier');

        $this->view('expenses/index', [
            'title' => 'Expenses',
            'expenses' => $expenseModel->getAll(),
            'suppliers' => $supplierModel->getAll(),
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $expenseModel = $this->model('Expense');

            $description = trim($_POST['description'] ?? '');
            $amount = (float)($_POST['amount'] ?? 0);
            $expenseDate = trim($_POST['expense_date'] ?? date('Y-m-d'));
            $category = trim($_POST['category'] ?? 'General');
            $supplierTag = trim($_POST['supplier_tag'] ?? '');

            if ($description === '' || $amount <= 0) {
                $_SESSION['error'] = 'Description and valid amount are required.';
                $this->redirect('expenses/index');
                return;
            }

            $notes = trim($_POST['notes'] ?? '');
            if ($supplierTag !== '') {
                $notes = trim($notes . ' | Supplier: ' . $supplierTag, ' |');
            }

            $expenseModel->create([
                'description' => $description,
                'amount' => $amount,
                'expense_date' => $expenseDate,
                'category' => $category,
                'notes' => $notes,
                'created_by' => $_SESSION['user']['id'] ?? null,
            ]);

            $_SESSION['success'] = 'Expense added successfully.';
        }

        $this->redirect('expenses/index');
    }
}
