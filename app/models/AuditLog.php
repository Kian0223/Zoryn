<?php
class AuditLog extends Model
{
    public function create(array $data): bool
    {
        $this->db->query("
            INSERT INTO audit_logs (module_name, action_type, reference_id, description, created_by, created_at)
            VALUES (:module_name, :action_type, :reference_id, :description, :created_by, NOW())
        ");
        $this->db->bind(':module_name', $data['module_name']);
        $this->db->bind(':action_type', $data['action_type']);
        $this->db->bind(':reference_id', $data['reference_id'] ?? null);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':created_by', $data['created_by'] ?? null);
        return $this->db->execute();
    }

    public function getRecent(int $limit = 200): array
    {
        $this->db->query("
            SELECT al.*, u.full_name
            FROM audit_logs al
            LEFT JOIN users u ON u.id = al.created_by
            ORDER BY al.created_at DESC, al.id DESC
            LIMIT :limit
        ");
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }
}
