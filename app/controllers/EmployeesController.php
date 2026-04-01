<?php
class EmployeesController extends BaseControllerWithPermissions
{
    public function index(): void
    {
        $this->requireModuleAccess('employees', 'can_view');
        $employeeModel = $this->model('Employee');
        $roleModel = $this->model('Role');
        $userModel = $this->model('User');

        $this->view('employees/index', [
            'title' => 'Employees',
            'employees' => $employeeModel->getAll(),
            'roles' => $roleModel->getAll(),
            'users' => method_exists($userModel, 'getAll') ? $userModel->getAll() : [],
        ]);
    }

    public function store(): void
    {
        $this->requireModuleAccess('employees', 'can_create');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('employees/index');
            return;
        }

        $employeeModel = $this->model('Employee');
        $ok = $employeeModel->create([
            'user_id' => (int)($_POST['user_id'] ?? 0),
            'full_name' => trim($_POST['full_name'] ?? ''),
            'role_id' => (int)($_POST['role_id'] ?? 0),
            'job_title' => trim($_POST['job_title'] ?? ''),
            'daily_rate' => (float)($_POST['daily_rate'] ?? 0),
            'hourly_rate' => (float)($_POST['hourly_rate'] ?? 0),
            'phone' => trim($_POST['phone'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'hire_date' => trim($_POST['hire_date'] ?? ''),
            'status' => trim($_POST['status'] ?? 'active'),
        ]);

        $_SESSION['success'] = $ok ? 'Employee added successfully.' : 'Failed to save employee.';
        $this->redirect('employees/index');
    }
}
