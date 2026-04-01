<?php
class Role extends Model
{
    public function getAll(): array
    {
        $this->db->query("SELECT * FROM roles ORDER BY role_name ASC");
        return $this->db->resultSet();
    }

    public function getPermissions(int $roleId): array
    {
        $this->db->query("SELECT * FROM role_permissions WHERE role_id = :role_id ORDER BY module_key ASC");
        $this->db->bind(':role_id', $roleId);
        return $this->db->resultSet();
    }

    public function savePermission(array $data): bool
    {
        $this->db->query("
            INSERT INTO role_permissions (role_id, module_key, can_view, can_create, can_edit, can_delete)
            VALUES (:role_id, :module_key, :can_view, :can_create, :can_edit, :can_delete)
            ON DUPLICATE KEY UPDATE
                can_view = VALUES(can_view),
                can_create = VALUES(can_create),
                can_edit = VALUES(can_edit),
                can_delete = VALUES(can_delete)
        ");
        $this->db->bind(':role_id', $data['role_id']);
        $this->db->bind(':module_key', $data['module_key']);
        $this->db->bind(':can_view', !empty($data['can_view']) ? 1 : 0);
        $this->db->bind(':can_create', !empty($data['can_create']) ? 1 : 0);
        $this->db->bind(':can_edit', !empty($data['can_edit']) ? 1 : 0);
        $this->db->bind(':can_delete', !empty($data['can_delete']) ? 1 : 0);
        return $this->db->execute();
    }
}
