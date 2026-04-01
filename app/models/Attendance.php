<?php
class Attendance extends Model
{
    public function getAll(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $where = [];
        if ($dateFrom) $where[] = "a.attendance_date >= :date_from";
        if ($dateTo) $where[] = "a.attendance_date <= :date_to";

        $sql = "SELECT a.*, e.employee_code, e.full_name, e.job_title
                FROM attendance_logs a
                LEFT JOIN employees e ON e.id = a.employee_id";
        if ($where) $sql .= " WHERE " . implode(" AND ", $where);
        $sql .= " ORDER BY a.attendance_date DESC, e.full_name ASC";

        $this->db->query($sql);
        if ($dateFrom) $this->db->bind(':date_from', $dateFrom);
        if ($dateTo) $this->db->bind(':date_to', $dateTo);
        return $this->db->resultSet();
    }

    public function upsert(array $data): bool
    {
        $hoursWorked = (float)($data['hours_worked'] ?? 0);
        $overtimeHours = (float)($data['overtime_hours'] ?? 0);

        $this->db->query("
            INSERT INTO attendance_logs (
                employee_id, attendance_date, time_in, time_out, hours_worked,
                overtime_hours, status, notes, created_at
            ) VALUES (
                :employee_id, :attendance_date, :time_in, :time_out, :hours_worked,
                :overtime_hours, :status, :notes, NOW()
            )
            ON DUPLICATE KEY UPDATE
                time_in = VALUES(time_in),
                time_out = VALUES(time_out),
                hours_worked = VALUES(hours_worked),
                overtime_hours = VALUES(overtime_hours),
                status = VALUES(status),
                notes = VALUES(notes)
        ");
        $this->db->bind(':employee_id', $data['employee_id']);
        $this->db->bind(':attendance_date', $data['attendance_date']);
        $this->db->bind(':time_in', $data['time_in'] ?: null);
        $this->db->bind(':time_out', $data['time_out'] ?: null);
        $this->db->bind(':hours_worked', $hoursWorked);
        $this->db->bind(':overtime_hours', $overtimeHours);
        $this->db->bind(':status', $data['status'] ?? 'present');
        $this->db->bind(':notes', $data['notes'] ?: null);
        return $this->db->execute();
    }
}
