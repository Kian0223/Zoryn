<?php
class APTermsController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $termModel = $this->model('APTerm');
        $supplierModel = $this->model('Supplier');

        $this->view('ap_terms/index', [
            'title' => 'AP Terms',
            'terms' => $termModel->getAll(),
            'suppliers' => $supplierModel->getAll(),
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('apterms/index');
            return;
        }

        $termName = trim($_POST['term_name'] ?? '');
        $daysDue = (int)($_POST['days_due'] ?? 0);
        $supplierId = (int)($_POST['supplier_id'] ?? 0);
        $isDefault = !empty($_POST['is_default']) ? 1 : 0;

        if ($termName === '') {
            $_SESSION['error'] = 'Term name is required.';
            $this->redirect('apterms/index');
            return;
        }

        $termModel = $this->model('APTerm');
        $termModel->create([
            'supplier_id' => $supplierId > 0 ? $supplierId : null,
            'term_name' => $termName,
            'days_due' => $daysDue,
            'is_default' => $isDefault,
        ]);

        $_SESSION['success'] = 'AP term saved successfully.';
        $this->redirect('apterms/index');
    }
}
