<?php
class Expense extends Model
{
    public function getAll(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $where = [];
        if ($dateFrom) $where[] = "expense_date >= :date_from";
        if ($dateTo) $where[] = "expense_date <= :date_to";

        $sql = "SELECT e.*, u.full_name AS created_by_name
                FROM expenses e
                LEFT JOIN users u ON u.id = e.created_by";
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY e.expense_date DESC, e.id DESC";

        $this->db->query($sql);
        if ($dateFrom) $this->db->bind(':date_from', $dateFrom);
        if ($dateTo) $this->db->bind(':date_to', $dateTo);

        return $this->db->resultSet();
    }

    public function create(array $data): bool
    {
        $this->db->query("
            INSERT INTO expenses (
                expense_date,
                description,
                category,
                amount,
                payment_method,
                notes,
                created_by
            ) VALUES (
                :expense_date,
                :description,
                :category,
                :amount,
                :payment_method,
                :notes,
                :created_by
            )
        ");

        $this->db->bind(':expense_date', $data['expense_date']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':category', $data['category']);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':payment_method', $data['payment_method'] ?: null);
        $this->db->bind(':notes', $data['notes'] ?: null);
        $this->db->bind(':created_by', $data['created_by'] ?? null);

        return $this->db->execute();
    }

    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM expenses WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getRangeTotal(?string $dateFrom = null, ?string $dateTo = null): float
    {
        $where = [];
        if ($dateFrom) $where[] = "expense_date >= :date_from";
        if ($dateTo) $where[] = "expense_date <= :date_to";

        $sql = "SELECT COALESCE(SUM(amount), 0) AS total FROM expenses";
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $this->db->query($sql);
        if ($dateFrom) $this->db->bind(':date_from', $dateFrom);
        if ($dateTo) $this->db->bind(':date_to', $dateTo);

        $row = $this->db->single();
        return (float)($row['total'] ?? 0);
    }

    public function getCurrentMonthTotal(): float
    {
        $this->db->query("
            SELECT COALESCE(SUM(amount), 0) AS total
            FROM expenses
            WHERE YEAR(expense_date) = YEAR(CURDATE())
              AND MONTH(expense_date) = MONTH(CURDATE())
        ");
        $row = $this->db->single();
        return (float)($row['total'] ?? 0);
    }

    public function getCategoryBreakdown(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $where = [];
        if ($dateFrom) $where[] = "expense_date >= :date_from";
        if ($dateTo) $where[] = "expense_date <= :date_to";

        $sql = "SELECT category, COALESCE(SUM(amount), 0) AS total
                FROM expenses";
        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " GROUP BY category ORDER BY total DESC, category ASC";

        $this->db->query($sql);
        if ($dateFrom) $this->db->bind(':date_from', $dateFrom);
        if ($dateTo) $this->db->bind(':date_to', $dateTo);

        return $this->db->resultSet();
    }
}
