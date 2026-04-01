<?php
class HRAnalytics extends Model
{
    public function getAttendanceSummary(string $dateFrom, string $dateTo): array
    {
        $this->db->query("
            SELECT
                COUNT(*) AS total_logs,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) AS present_count,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) AS late_count,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) AS absent_count,
                SUM(CASE WHEN status = 'half_day' THEN 1 ELSE 0 END) AS half_day_count,
                SUM(CASE WHEN status = 'leave' THEN 1 ELSE 0 END) AS leave_count,
                COALESCE(SUM(hours_worked), 0) AS total_hours,
                COALESCE(SUM(overtime_hours), 0) AS total_overtime
            FROM attendance_logs
            WHERE attendance_date BETWEEN :date_from AND :date_to
        ");
        $this->db->bind(':date_from', $dateFrom);
        $this->db->bind(':date_to', $dateTo);
        return $this->db->single() ?: [
            'total_logs'=>0,'present_count'=>0,'late_count'=>0,'absent_count'=>0,
            'half_day_count'=>0,'leave_count'=>0,'total_hours'=>0,'total_overtime'=>0
        ];
    }

    public function getLeaveSummary(string $dateFrom, string $dateTo): array
    {
        $this->db->query("
            SELECT
                COUNT(*) AS total_requests,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved_count,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected_count,
                COALESCE(SUM(CASE WHEN status = 'approved' THEN days_count ELSE 0 END), 0) AS approved_days
            FROM leave_requests
            WHERE date_from <= :date_to
              AND date_to >= :date_from
        ");
        $this->db->bind(':date_from', $dateFrom);
        $this->db->bind(':date_to', $dateTo);
        return $this->db->single() ?: [
            'total_requests'=>0,'pending_count'=>0,'approved_count'=>0,'rejected_count'=>0,'approved_days'=>0
        ];
    }

    public function getTopLateEmployees(string $dateFrom, string $dateTo, int $limit = 10): array
    {
        $this->db->query("
            SELECT
                e.full_name,
                e.employee_code,
                COUNT(a.id) AS late_count
            FROM attendance_logs a
            INNER JOIN employees e ON e.id = a.employee_id
            WHERE a.attendance_date BETWEEN :date_from AND :date_to
              AND a.status = 'late'
            GROUP BY e.id, e.full_name, e.employee_code
            ORDER BY late_count DESC, e.full_name ASC
            LIMIT :limit
        ");
        $this->db->bind(':date_from', $dateFrom);
        $this->db->bind(':date_to', $dateTo);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }

    public function getTopOvertimeEmployees(string $dateFrom, string $dateTo, int $limit = 10): array
    {
        $this->db->query("
            SELECT
                e.full_name,
                e.employee_code,
                COALESCE(SUM(a.overtime_hours), 0) AS overtime_hours
            FROM attendance_logs a
            INNER JOIN employees e ON e.id = a.employee_id
            WHERE a.attendance_date BETWEEN :date_from AND :date_to
            GROUP BY e.id, e.full_name, e.employee_code
            ORDER BY overtime_hours DESC, e.full_name ASC
            LIMIT :limit
        ");
        $this->db->bind(':date_from', $dateFrom);
        $this->db->bind(':date_to', $dateTo);
        $this->db->bind(':limit', $limit);
        return $this->db->resultSet();
    }
}
