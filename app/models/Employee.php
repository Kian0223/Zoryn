<?php
class Employee extends Model
{
    public function getAll(): array
    {
        $this->db->query("
            SELECT e.*, r.role_name, u.username
            FROM employees e
            LEFT JOIN roles r ON r.id = e.role_id
            LEFT JOIN users u ON u.id = e.user_id
            ORDER BY e.full_name ASC
        ");
        return $this->db->resultSet();
    }

    public function getPayrollSummary(string $dateFrom, string $dateTo): array
    {
        $this->db->query("
            SELECT
                e.id, e.employee_code, e.full_name, e.job_title, e.daily_rate, e.hourly_rate,
                COALESCE(COUNT(a.id), 0) AS attendance_days,
                COALESCE(SUM(a.hours_worked), 0) AS total_hours,
                COALESCE(SUM(a.overtime_hours), 0) AS overtime_hours,
                COALESCE(SUM(a.hours_worked * e.hourly_rate), 0) AS regular_pay,
                COALESCE(SUM(a.overtime_hours * e.hourly_rate * 1.25), 0) AS overtime_pay,
                COALESCE(SUM(a.hours_worked * e.hourly_rate), 0) + COALESCE(SUM(a.overtime_hours * e.hourly_rate * 1.25), 0) AS base_gross_pay
            FROM employees e
            LEFT JOIN attendance_logs a
              ON a.employee_id = e.id
             AND a.attendance_date BETWEEN :date_from AND :date_to
             AND a.status IN ('present','late','half_day')
            GROUP BY e.id, e.employee_code, e.full_name, e.job_title, e.daily_rate, e.hourly_rate
            ORDER BY e.full_name ASC
        ");
        $this->db->bind(':date_from', $dateFrom);
        $this->db->bind(':date_to', $dateTo);
        return $this->db->resultSet();
    }
}
