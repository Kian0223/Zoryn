<?php
class Reservation extends Model
{
    public function getAll(): array
    {
        $this->db->query("
            SELECT r.*, dt.table_name, dt.capacity AS table_capacity, u.full_name AS created_by_name
            FROM reservations r
            LEFT JOIN dining_tables dt ON dt.id = r.table_id
            LEFT JOIN users u ON u.id = r.created_by
            ORDER BY r.reservation_date ASC, r.reservation_time ASC, r.id DESC
        ");
        return $this->db->resultSet();
    }

    public function getCalendarData(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $dateFrom = $dateFrom ?: date('Y-m-01');
        $dateTo = $dateTo ?: date('Y-m-t');
        $this->db->query("
            SELECT r.*, dt.table_name
            FROM reservations r
            LEFT JOIN dining_tables dt ON dt.id = r.table_id
            WHERE r.reservation_date BETWEEN :date_from AND :date_to
            ORDER BY r.reservation_date ASC, r.reservation_time ASC
        ");
        $this->db->bind(':date_from', $dateFrom);
        $this->db->bind(':date_to', $dateTo);
        return $this->db->resultSet();
    }

    public function getTodaySummary(): array
    {
        $this->db->query("
            SELECT COUNT(*) AS total_today,
                   SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_today,
                   SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed_today,
                   SUM(CASE WHEN status = 'seated' THEN 1 ELSE 0 END) AS seated_today
            FROM reservations
            WHERE reservation_date = CURDATE()
        ");
        return $this->db->single() ?: ['total_today'=>0,'pending_today'=>0,'confirmed_today'=>0,'seated_today'=>0];
    }

    public function create(array $data): int|false
    {
        $reservationNo = 'RSV-' . date('Ymd-His') . '-' . rand(100, 999);
        $this->db->query("
            INSERT INTO reservations (
                reservation_no, customer_name, customer_phone, customer_email, pax_count,
                reservation_date, reservation_time, table_id, status, notes, created_by, created_at
            ) VALUES (
                :reservation_no, :customer_name, :customer_phone, :customer_email, :pax_count,
                :reservation_date, :reservation_time, :table_id, :status, :notes, :created_by, NOW()
            )
        ");
        $this->db->bind(':reservation_no', $reservationNo);
        $this->db->bind(':customer_name', $data['customer_name']);
        $this->db->bind(':customer_phone', $data['customer_phone'] ?: null);
        $this->db->bind(':customer_email', $data['customer_email'] ?: null);
        $this->db->bind(':pax_count', $data['pax_count']);
        $this->db->bind(':reservation_date', $data['reservation_date']);
        $this->db->bind(':reservation_time', $data['reservation_time']);
        $this->db->bind(':table_id', $data['table_id'] ?: null);
        $this->db->bind(':status', $data['status'] ?? 'pending');
        $this->db->bind(':notes', $data['notes'] ?: null);
        $this->db->bind(':created_by', $data['created_by'] ?? null);
        if (!$this->db->execute()) return false;
        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id): array|false
    {
        $this->db->query("SELECT r.*, dt.table_name FROM reservations r LEFT JOIN dining_tables dt ON dt.id = r.table_id WHERE r.id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function update(int $id, array $data): bool
    {
        $this->db->query("
            UPDATE reservations
            SET customer_name = :customer_name,
                customer_phone = :customer_phone,
                customer_email = :customer_email,
                pax_count = :pax_count,
                reservation_date = :reservation_date,
                reservation_time = :reservation_time,
                table_id = :table_id,
                status = :status,
                notes = :notes,
                updated_at = NOW()
            WHERE id = :id
        ");
        $this->db->bind(':customer_name', $data['customer_name']);
        $this->db->bind(':customer_phone', $data['customer_phone'] ?: null);
        $this->db->bind(':customer_email', $data['customer_email'] ?: null);
        $this->db->bind(':pax_count', $data['pax_count']);
        $this->db->bind(':reservation_date', $data['reservation_date']);
        $this->db->bind(':reservation_time', $data['reservation_time']);
        $this->db->bind(':table_id', $data['table_id'] ?: null);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':notes', $data['notes'] ?: null);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $allowed = ['pending','confirmed','seated','completed','cancelled','no_show'];
        if (!in_array($status, $allowed, true)) return false;
        $this->db->query("UPDATE reservations SET status = :status, updated_at = NOW() WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM reservations WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function logAction(int $reservationId, string $actionType, ?string $note, ?int $createdBy): bool
    {
        $this->db->query("
            INSERT INTO reservation_logs (reservation_id, action_type, action_note, created_by, created_at)
            VALUES (:reservation_id, :action_type, :action_note, :created_by, NOW())
        ");
        $this->db->bind(':reservation_id', $reservationId);
        $this->db->bind(':action_type', $actionType);
        $this->db->bind(':action_note', $note ?: null);
        $this->db->bind(':created_by', $createdBy);
        return $this->db->execute();
    }
}
