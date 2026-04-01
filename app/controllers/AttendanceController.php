<?php
class AttendanceController extends BaseControllerWithPermissions
{
    public function index(): void
    {
        $this->requireModuleAccess('attendance', 'can_view');
        $attendanceModel = $this->model('Attendance');
        $employeeModel = $this->model('Employee');

        $dateFrom = trim($_GET['date_from'] ?? date('Y-m-01'));
        $dateTo = trim($_GET['date_to'] ?? date('Y-m-d'));

        $this->view('attendance/index', [
            'title' => 'Attendance',
            'attendance_rows' => $attendanceModel->getAll($dateFrom, $dateTo),
            'employees' => $employeeModel->getAll(),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);
    }

    public function store(): void
    {
        $this->requireModuleAccess('attendance', 'can_create');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('attendance/index');
            return;
        }

        $attendanceModel = $this->model('Attendance');
        $ok = $attendanceModel->upsert([
            'employee_id' => (int)($_POST['employee_id'] ?? 0),
            'attendance_date' => trim($_POST['attendance_date'] ?? date('Y-m-d')),
            'time_in' => trim($_POST['time_in'] ?? ''),
            'time_out' => trim($_POST['time_out'] ?? ''),
            'hours_worked' => (float)($_POST['hours_worked'] ?? 0),
            'overtime_hours' => (float)($_POST['overtime_hours'] ?? 0),
            'status' => trim($_POST['status'] ?? 'present'),
            'notes' => trim($_POST['notes'] ?? ''),
        ]);

        $_SESSION['success'] = $ok ? 'Attendance saved successfully.' : 'Failed to save attendance.';
        $this->redirect('attendance/index');
    }
}
