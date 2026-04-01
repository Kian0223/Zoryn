<?php
class PayrollAdjustment extends Model
{
    public function getAll(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $where = [];
        if ($dateFrom) $where[] = "pa.adjustment_date >= :date_from";
        if ($dateTo) $where[] = "pa.adjustment_date <= :date_to";

        $sql = "
            SELECT pa.*, e.employee_code, e.full_name
            FROM payroll_adjustments pa
            LEFT JOIN employees e ON e.id = pa.employee_id
        ";
        if ($where) $sql .= " WHERE " . implode(" AND ", $where);
        $sql .= " ORDER BY pa.adjustment_date DESC, pa.id DESC";

        $this->db->query($sql);
        if ($dateFrom) $this->db->bind(':date_from', $dateFrom);
        if ($dateTo) $this->db->bind(':date_to', $dateTo);
        return $this->db->resultSet();
    }

    public function create(array $data): bool
    {
        $this->db->query("
            INSERT INTO payroll_adjustments (
                employee_id, adjustment_date, adjustment_type, adjustment_name, amount, notes, created_at
            ) VALUES (
                :employee_id, :adjustment_date, :adjustment_type, :adjustment_name, :amount, :notes, NOW()
            )
        ");
        $this->db->bind(':employee_id', $data['employee_id']);
        $this->db->bind(':adjustment_date', $data['adjustment_date']);
        $this->db->bind(':adjustment_type', $data['adjustment_type']);
        $this->db->bind(':adjustment_name', $data['adjustment_name']);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':notes', $data['notes'] ?: null);
        return $this->db->execute();
    }

    public function getSummaryByEmployee(string $dateFrom, string $dateTo): array
    {
        $this->db->query("
            SELECT
                employee_id,
                SUM(CASE WHEN adjustment_type = 'allowance' THEN amount ELSE 0 END) AS total_allowances,
                SUM(CASE WHEN adjustment_type = 'deduction' THEN amount ELSE 0 END) AS total_deductions
            FROM payroll_adjustments
            WHERE adjustment_date BETWEEN :date_from AND :date_to
            GROUP BY employee_id
        ");
        $this->db->bind(':date_from', $dateFrom);
        $this->db->bind(':date_to', $dateTo);
        return $this->db->resultSet();
    }
}
