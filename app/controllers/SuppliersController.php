<?php
class SuppliersController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $supplierModel = $this->model('Supplier');

        $this->view('suppliers/index', [
            'title' => 'Suppliers',
            'suppliers' => $supplierModel->getAll(),
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $supplierModel = $this->model('Supplier');
            $name = trim($_POST['supplier_name'] ?? '');

            if ($name === '') {
                $_SESSION['error'] = 'Supplier name is required.';
                $this->redirect('suppliers/index');
                return;
            }

            $supplierModel->create([
                'supplier_name' => $name,
                'contact_person' => trim($_POST['contact_person'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'notes' => trim($_POST['notes'] ?? ''),
            ]);

            $_SESSION['success'] = 'Supplier added successfully.';
        }

        $this->redirect('suppliers/index');
    }

    public function update($id): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $supplierModel = $this->model('Supplier');
            $name = trim($_POST['supplier_name'] ?? '');

            if ($name === '') {
                $_SESSION['error'] = 'Supplier name is required.';
                $this->redirect('suppliers/index');
                return;
            }

            $supplierModel->update((int)$id, [
                'supplier_name' => $name,
                'contact_person' => trim($_POST['contact_person'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'notes' => trim($_POST['notes'] ?? ''),
            ]);

            $_SESSION['success'] = 'Supplier updated successfully.';
        }

        $this->redirect('suppliers/index');
    }

    public function delete($id): void
    {
        $this->requireLogin();
        $supplierModel = $this->model('Supplier');
        $supplierModel->delete((int)$id);
        $_SESSION['success'] = 'Supplier deleted successfully.';
        $this->redirect('suppliers/index');
    }
}
