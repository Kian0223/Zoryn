<?php
class APTerm extends Model
{
    public function getAll(): array
    {
        $this->db->query("
            SELECT t.*, s.supplier_name
            FROM ap_terms t
            LEFT JOIN suppliers s ON s.id = t.supplier_id
            ORDER BY t.is_default DESC, t.term_name ASC
        ");
        return $this->db->resultSet();
    }

    public function create(array $data): bool
    {
        if (!empty($data['is_default'])) {
            $this->db->query("UPDATE ap_terms SET is_default = 0");
            $this->db->execute();
        }

        $this->db->query("
            INSERT INTO ap_terms (supplier_id, term_name, days_due, is_default, created_at)
            VALUES (:supplier_id, :term_name, :days_due, :is_default, NOW())
        ");
        $this->db->bind(':supplier_id', $data['supplier_id'] ?: null);
        $this->db->bind(':term_name', $data['term_name']);
        $this->db->bind(':days_due', $data['days_due']);
        $this->db->bind(':is_default', !empty($data['is_default']) ? 1 : 0);
        return $this->db->execute();
    }

    public function getDefaultDays(): int
    {
        $this->db->query("SELECT days_due FROM ap_terms WHERE is_default = 1 ORDER BY id DESC LIMIT 1");
        $row = $this->db->single();
        return (int)($row['days_due'] ?? 0);
    }
}
