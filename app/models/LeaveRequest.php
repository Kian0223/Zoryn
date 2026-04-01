<?php
class LeaveRequest extends Model
{
    public function getAll(): array
    {
        $this->db->query("
            SELECT lr.*, e.employee_code, e.full_name, approver.full_name AS approver_name
            FROM leave_requests lr
            LEFT JOIN employees e ON e.id = lr.employee_id
            LEFT JOIN employees approver ON approver.id = lr.approved_by
            ORDER BY lr.created_at DESC, lr.id DESC
        ");
        return $this->db->resultSet();
    }

    public function create(array $data): bool
    {
        $this->db->query("
            INSERT INTO leave_requests (
                employee_id, leave_type, date_from, date_to, days_count, reason, status, created_at
            ) VALUES (
                :employee_id, :leave_type, :date_from, :date_to, :days_count, :reason, 'pending', NOW()
            )
        ");
        $this->db->bind(':employee_id', $data['employee_id']);
        $this->db->bind(':leave_type', $data['leave_type']);
        $this->db->bind(':date_from', $data['date_from']);
        $this->db->bind(':date_to', $data['date_to']);
        $this->db->bind(':days_count', $data['days_count']);
        $this->db->bind(':reason', $data['reason'] ?: null);
        return $this->db->execute();
    }

    public function updateStatus(int $id, string $status, ?int $approvedBy = null): bool
    {
        $this->db->query("
            UPDATE leave_requests
            SET status = :status,
                approved_by = :approved_by,
                approved_at = NOW()
            WHERE id = :id
        ");
        $this->db->bind(':status', $status);
        $this->db->bind(':approved_by', $approvedBy);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
