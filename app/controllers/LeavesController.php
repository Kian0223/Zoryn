<?php
class LeavesController extends BaseControllerWithPermissions
{
    public function index(): void
    {
        $this->requireModuleAccess('leaves', 'can_view');
        $leaveModel = $this->model('LeaveRequest');
        $employeeModel = $this->model('Employee');

        $this->view('leaves/index', [
            'title' => 'Leave Requests',
            'leave_requests' => $leaveModel->getAll(),
            'employees' => $employeeModel->getAll(),
        ]);
    }

    public function store(): void
    {
        $this->requireModuleAccess('leaves', 'can_create');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('leaves/index');
            return;
        }

        $leaveModel = $this->model('LeaveRequest');
        $ok = $leaveModel->create([
            'employee_id' => (int)($_POST['employee_id'] ?? 0),
            'leave_type' => trim($_POST['leave_type'] ?? 'vacation'),
            'date_from' => trim($_POST['date_from'] ?? ''),
            'date_to' => trim($_POST['date_to'] ?? ''),
            'days_count' => (float)($_POST['days_count'] ?? 1),
            'reason' => trim($_POST['reason'] ?? ''),
        ]);

        $_SESSION['success'] = $ok ? 'Leave request submitted successfully.' : 'Failed to save leave request.';
        $this->redirect('leaves/index');
    }

    public function approve($id): void
    {
        $this->requireModuleAccess('leaves', 'can_edit');
        $leaveModel = $this->model('LeaveRequest');
        $leaveModel->updateStatus((int)$id, 'approved', null);
        $_SESSION['success'] = 'Leave request approved.';
        $this->redirect('leaves/index');
    }

    public function reject($id): void
    {
        $this->requireModuleAccess('leaves', 'can_edit');
        $leaveModel = $this->model('LeaveRequest');
        $leaveModel->updateStatus((int)$id, 'rejected', null);
        $_SESSION['success'] = 'Leave request rejected.';
        $this->redirect('leaves/index');
    }
}
