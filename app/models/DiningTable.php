<?php
class DiningTable extends Model
{
    public function getAll(): array
    {
        $this->db->query("SELECT * FROM dining_tables ORDER BY table_name ASC");
        return $this->db->resultSet();
    }

    public function getSummary(): array
    {
        $this->db->query("
            SELECT
                COUNT(*) AS total_tables,
                SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) AS available_tables,
                SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) AS occupied_tables,
                SUM(CASE WHEN status = 'reserved' THEN 1 ELSE 0 END) AS reserved_tables
            FROM dining_tables
        ");
        return $this->db->single() ?: [
            'total_tables' => 0,
            'available_tables' => 0,
            'occupied_tables' => 0,
            'reserved_tables' => 0,
        ];
    }

    public function findById(int $id): array|false
    {
        $this->db->query("SELECT * FROM dining_tables WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function create(array $data): bool
    {
        $this->db->query("
            INSERT INTO dining_tables (table_name, capacity, area, status)
            VALUES (:table_name, :capacity, :area, :status)
        ");
        $this->db->bind(':table_name', $data['table_name']);
        $this->db->bind(':capacity', $data['capacity']);
        $this->db->bind(':area', $data['area'] ?: null);
        $this->db->bind(':status', $data['status'] ?? 'available');
        return $this->db->execute();
    }

    public function update(int $id, array $data): bool
    {
        $this->db->query("
            UPDATE dining_tables
            SET table_name = :table_name,
                capacity = :capacity,
                area = :area,
                status = :status
            WHERE id = :id
        ");
        $this->db->bind(':table_name', $data['table_name']);
        $this->db->bind(':capacity', $data['capacity']);
        $this->db->bind(':area', $data['area'] ?: null);
        $this->db->bind(':status', $data['status'] ?? 'available');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function setStatus(int $id, string $status): bool
    {
        $allowed = ['available', 'occupied', 'reserved', 'maintenance'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $this->db->query("UPDATE dining_tables SET status = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM dining_tables WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
