<?php
class CustomersController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $customerModel = $this->model('Customer');

        $this->view('customers/index', [
            'title' => 'Customers',
            'customers' => $customerModel->getAll(),
            'summary' => $customerModel->getSummary(),
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('customers/index');
            return;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        if ($fullName === '') {
            $_SESSION['error'] = 'Customer name is required.';
            $this->redirect('customers/index');
            return;
        }

        $customerModel = $this->model('Customer');
        $ok = $customerModel->create([
            'full_name' => $fullName,
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'birthdate' => trim($_POST['birthdate'] ?? ''),
        ]);

        $_SESSION['success'] = $ok ? 'Customer added successfully.' : 'Failed to save customer.';
        $this->redirect('customers/index');
    }

    public function update($id): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('customers/index');
            return;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        if ($fullName === '') {
            $_SESSION['error'] = 'Customer name is required.';
            $this->redirect('customers/index');
            return;
        }

        $customerModel = $this->model('Customer');
        $ok = $customerModel->update((int)$id, [
            'full_name' => $fullName,
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'birthdate' => trim($_POST['birthdate'] ?? ''),
        ]);

        $_SESSION['success'] = $ok ? 'Customer updated successfully.' : 'Failed to update customer.';
        $this->redirect('customers/index');
    }

    public function delete($id): void
    {
        $this->requireLogin();
        $customerModel = $this->model('Customer');
        $ok = $customerModel->delete((int)$id);
        $_SESSION['success'] = $ok ? 'Customer deleted successfully.' : 'Failed to delete customer.';
        $this->redirect('customers/index');
    }
}
