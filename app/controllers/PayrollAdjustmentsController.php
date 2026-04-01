<?php
class PayrollAdjustmentsController extends BaseControllerWithPermissions
{
    public function index(): void
    {
        $this->requireModuleAccess('payroll', 'can_view');
        $adjustmentModel = $this->model('PayrollAdjustment');
        $employeeModel = $this->model('Employee');

        $dateFrom = trim($_GET['date_from'] ?? date('Y-m-01'));
        $dateTo = trim($_GET['date_to'] ?? date('Y-m-d'));

        $this->view('payroll_adjustments/index', [
            'title' => 'Payroll Adjustments',
            'adjustments' => $adjustmentModel->getAll($dateFrom, $dateTo),
            'employees' => $employeeModel->getAll(),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);
    }

    public function store(): void
    {
        $this->requireModuleAccess('payroll', 'can_create');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('payrolladjustments/index');
            return;
        }

        $adjustmentModel = $this->model('PayrollAdjustment');
        $ok = $adjustmentModel->create([
            'employee_id' => (int)($_POST['employee_id'] ?? 0),
            'adjustment_date' => trim($_POST['adjustment_date'] ?? date('Y-m-d')),
            'adjustment_type' => trim($_POST['adjustment_type'] ?? 'allowance'),
            'adjustment_name' => trim($_POST['adjustment_name'] ?? ''),
            'amount' => (float)($_POST['amount'] ?? 0),
            'notes' => trim($_POST['notes'] ?? ''),
        ]);

        $_SESSION['success'] = $ok ? 'Payroll adjustment saved successfully.' : 'Failed to save adjustment.';
        $this->redirect('payrolladjustments/index');
    }
}
