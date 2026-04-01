<?php
class AuditLogsController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $auditModel = $this->model('AuditLog');

        $this->view('audit_logs/index', [
            'title' => 'Audit Trail',
            'logs' => $auditModel->getRecent(200),
        ]);
    }
}
