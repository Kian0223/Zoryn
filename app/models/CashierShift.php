<?php
class CashierShift extends Model
{
    public function getOpenShiftByUser(int $userId): array|false
    {
        $this->db->query("
            SELECT *
            FROM cashier_shifts
            WHERE user_id = :user_id AND status = 'open'
            ORDER BY id DESC
            LIMIT 1
        ");
        $this->db->bind(':user_id', $userId);
        return $this->db->single();
    }

    public function openShift(array $data): bool
    {
        $this->db->query("
            INSERT INTO cashier_shifts (user_id, shift_date, opening_cash, status, opened_at, notes)
            VALUES (:user_id, :shift_date, :opening_cash, 'open', NOW(), :notes)
        ");
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':shift_date', $data['shift_date']);
        $this->db->bind(':opening_cash', $data['opening_cash']);
        $this->db->bind(':notes', $data['notes'] ?? null);
        return $this->db->execute();
    }

    public function closeShift(int $id, array $data): bool
    {
        $this->db->query("
            UPDATE cashier_shifts
            SET closing_cash = :closing_cash,
                expected_cash = :expected_cash,
                cash_difference = :cash_difference,
                status = 'closed',
                closed_at = NOW(),
                notes = :notes
            WHERE id = :id
        ");
        $this->db->bind(':closing_cash', $data['closing_cash']);
        $this->db->bind(':expected_cash', $data['expected_cash']);
        $this->db->bind(':cash_difference', $data['cash_difference']);
        $this->db->bind(':notes', $data['notes'] ?? null);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getRecent(): array
    {
        $this->db->query("
            SELECT cs.*, u.full_name
            FROM cashier_shifts cs
            LEFT JOIN users u ON u.id = cs.user_id
            ORDER BY cs.id DESC
            LIMIT 50
        ");
        return $this->db->resultSet();
    }
}
