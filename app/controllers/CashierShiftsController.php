<?php
class CashierShiftsController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $shiftModel = $this->model('CashierShift');
        $saleModel = $this->model('Sale');
        $userId = (int)($_SESSION['user']['id'] ?? 0);

        $this->view('cashier_shifts/index', [
            'title' => 'Cashier Shifts',
            'open_shift' => $shiftModel->getOpenShiftByUser($userId),
            'recent_shifts' => $shiftModel->getRecent(),
            'today_cash_sales' => method_exists($saleModel, 'getCashSalesTotalToday') ? $saleModel->getCashSalesTotalToday() : 0,
        ]);
    }

    public function open(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('cashiershifts/index');
            return;
        }

        $shiftModel = $this->model('CashierShift');
        $auditModel = $this->model('AuditLog');
        $userId = (int)($_SESSION['user']['id'] ?? 0);

        if ($shiftModel->getOpenShiftByUser($userId)) {
            $_SESSION['error'] = 'You already have an open shift.';
            $this->redirect('cashiershifts/index');
            return;
        }

        $openingCash = (float)($_POST['opening_cash'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');

        if ($shiftModel->openShift([
            'user_id' => $userId,
            'shift_date' => date('Y-m-d'),
            'opening_cash' => $openingCash,
            'notes' => $notes,
        ])) {
            $auditModel->create([
                'module_name' => 'cashier_shift',
                'action_type' => 'open',
                'description' => 'Shift opened with ₱' . number_format($openingCash, 2),
                'created_by' => $userId,
            ]);
            $_SESSION['success'] = 'Shift opened successfully.';
        } else {
            $_SESSION['error'] = 'Failed to open shift.';
        }

        $this->redirect('cashiershifts/index');
    }

    public function close($id): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('cashiershifts/index');
            return;
        }

        $shiftModel = $this->model('CashierShift');
        $saleModel = $this->model('Sale');
        $auditModel = $this->model('AuditLog');

        $closingCash = (float)($_POST['closing_cash'] ?? 0);
        $openingCash = (float)($_POST['opening_cash_hidden'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        $expectedCash = (method_exists($saleModel, 'getCashSalesTotalToday') ? $saleModel->getCashSalesTotalToday() : 0) + $openingCash;
        $difference = $closingCash - $expectedCash;

        if ($shiftModel->closeShift((int)$id, [
            'closing_cash' => $closingCash,
            'expected_cash' => $expectedCash,
            'cash_difference' => $difference,
            'notes' => $notes,
        ])) {
            $auditModel->create([
                'module_name' => 'cashier_shift',
                'action_type' => 'close',
                'reference_id' => (int)$id,
                'description' => 'Shift closed. Difference: ₱' . number_format($difference, 2),
                'created_by' => (int)($_SESSION['user']['id'] ?? 0),
            ]);
            $_SESSION['success'] = 'Shift closed successfully.';
        } else {
            $_SESSION['error'] = 'Failed to close shift.';
        }

        $this->redirect('cashiershifts/index');
    }
}
