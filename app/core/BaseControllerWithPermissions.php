<?php
class BaseControllerWithPermissions extends Controller
{
    protected function requireModuleAccess(string $moduleKey, string $action = 'can_view'): void
    {
        $this->requireLogin();

        $roleId = (int)($_SESSION['user']['role_id'] ?? 0);
        $db = new Database();

        if (!PermissionMiddleware::canAccess($db, $roleId, $moduleKey, $action)) {
            $_SESSION['error'] = 'You do not have permission to access this page.';
            $this->redirect('dashboard/index');
            exit;
        }
    }
}
