<?php
class PermissionMiddleware
{
    public static function canAccess(Database $db, ?int $roleId, string $moduleKey, string $action = 'can_view'): bool
    {
        if (empty($roleId)) return false;

        if ((int)$roleId === 1) return true; // Admin full access

        $allowedActions = ['can_view', 'can_create', 'can_edit', 'can_delete'];
        if (!in_array($action, $allowedActions, true)) {
            $action = 'can_view';
        }

        $db->query("
            SELECT {$action} AS allowed
            FROM role_permissions
            WHERE role_id = :role_id AND module_key = :module_key
            LIMIT 1
        ");
        $db->bind(':role_id', $roleId);
        $db->bind(':module_key', $moduleKey);
        $row = $db->single();

        return !empty($row['allowed']);
    }
}
