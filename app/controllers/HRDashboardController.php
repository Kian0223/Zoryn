<?php
class HRDashboardController extends BaseControllerWithPermissions
{
    public function index(): void
    {
        $this->requireModuleAccess('hr_dashboard', 'can_view');
        $hrModel = $this->model('HRAnalytics');

        $dateFrom = trim($_GET['date_from'] ?? date('Y-m-01'));
        $dateTo = trim($_GET['date_to'] ?? date('Y-m-d'));

        $this->view('hr_dashboard/index', [
            'title' => 'HR Dashboard',
            'attendance_summary' => $hrModel->getAttendanceSummary($dateFrom, $dateTo),
            'leave_summary' => $hrModel->getLeaveSummary($dateFrom, $dateTo),
            'late_employees' => $hrModel->getTopLateEmployees($dateFrom, $dateTo, 10),
            'overtime_employees' => $hrModel->getTopOvertimeEmployees($dateFrom, $dateTo, 10),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);
    }
}
