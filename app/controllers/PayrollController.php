<?php
class PayrollController extends BaseControllerWithPermissions
{
    public function index(): void
    {
        $this->requireModuleAccess('payroll', 'can_view');
        $employeeModel = $this->model('Employee');
        $adjustmentModel = $this->model('PayrollAdjustment');

        $dateFrom = trim($_GET['date_from'] ?? date('Y-m-01'));
        $dateTo = trim($_GET['date_to'] ?? date('Y-m-d'));

        $rows = $employeeModel->getPayrollSummary($dateFrom, $dateTo);
        $adjRows = $adjustmentModel->getSummaryByEmployee($dateFrom, $dateTo);
        $adjMap = [];
        foreach ($adjRows as $adj) {
            $adjMap[(int)$adj['employee_id']] = $adj;
        }

        foreach ($rows as &$row) {
            $empId = (int)$row['id'];
            $allowances = (float)($adjMap[$empId]['total_allowances'] ?? 0);
            $deductions = (float)($adjMap[$empId]['total_deductions'] ?? 0);
            $row['total_allowances'] = $allowances;
            $row['total_deductions'] = $deductions;
            $row['gross_pay'] = (float)$row['base_gross_pay'] + $allowances;
            $row['net_pay'] = $row['gross_pay'] - $deductions;
        }

        $this->view('payroll/index', [
            'title' => 'Payroll Summary',
            'rows' => $rows,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);
    }

    public function payslip($employeeId): void
    {
        $this->requireModuleAccess('payroll', 'can_view');
        $employeeModel = $this->model('Employee');
        $adjustmentModel = $this->model('PayrollAdjustment');

        $dateFrom = trim($_GET['date_from'] ?? date('Y-m-01'));
        $dateTo = trim($_GET['date_to'] ?? date('Y-m-d'));

        $rows = $employeeModel->getPayrollSummary($dateFrom, $dateTo);
        $employeeRow = null;
        foreach ($rows as $row) {
            if ((int)$row['id'] === (int)$employeeId) {
                $employeeRow = $row;
                break;
            }
        }

        if (!$employeeRow) {
            $_SESSION['error'] = 'Employee payroll record not found.';
            $this->redirect('payroll/index');
            return;
        }

        $adjRows = $adjustmentModel->getAll($dateFrom, $dateTo);
        $employeeAdjustments = [];
        $allowances = 0;
        $deductions = 0;
        foreach ($adjRows as $adj) {
            if ((int)$adj['employee_id'] === (int)$employeeId) {
                $employeeAdjustments[] = $adj;
                if (($adj['adjustment_type'] ?? '') === 'allowance') $allowances += (float)$adj['amount'];
                if (($adj['adjustment_type'] ?? '') === 'deduction') $deductions += (float)$adj['amount'];
            }
        }

        $employeeRow['gross_pay'] = (float)$employeeRow['base_gross_pay'] + $allowances;
        $employeeRow['net_pay'] = $employeeRow['gross_pay'] - $deductions;

        $this->view('payroll/payslip', [
            'title' => 'Payslip',
            'employee' => $employeeRow,
            'adjustments' => $employeeAdjustments,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'allowances' => $allowances,
            'deductions' => $deductions,
        ]);
    }
}
