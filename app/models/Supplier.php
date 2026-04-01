<?php
class Supplier extends Model
{
    public function getAll(): array
    {
        $this->db->query("
            SELECT s.*,
                   COALESCE(SUM(CASE WHEN gp.status = 'received' THEN gp.balance_due ELSE 0 END), 0) AS total_balance_due
            FROM suppliers s
            LEFT JOIN grocery_purchases gp ON gp.supplier_id = s.id
            GROUP BY s.id
            ORDER BY s.supplier_name ASC
        ");
        return $this->db->resultSet();
    }

    public function findById(int $id): array|false
    {
        $this->db->query("SELECT * FROM suppliers WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function create(array $data): bool
    {
        $this->db->query("
            INSERT INTO suppliers (
                supplier_name, contact_person, phone, email, address, notes, created_at
            ) VALUES (
                :supplier_name, :contact_person, :phone, :email, :address, :notes, NOW()
            )
        ");
        $this->db->bind(':supplier_name', $data['supplier_name']);
        $this->db->bind(':contact_person', $data['contact_person'] ?: null);
        $this->db->bind(':phone', $data['phone'] ?: null);
        $this->db->bind(':email', $data['email'] ?: null);
        $this->db->bind(':address', $data['address'] ?: null);
        $this->db->bind(':notes', $data['notes'] ?: null);
        return $this->db->execute();
    }

    public function update(int $id, array $data): bool
    {
        $this->db->query("
            UPDATE suppliers
            SET supplier_name = :supplier_name,
                contact_person = :contact_person,
                phone = :phone,
                email = :email,
                address = :address,
                notes = :notes
            WHERE id = :id
        ");
        $this->db->bind(':supplier_name', $data['supplier_name']);
        $this->db->bind(':contact_person', $data['contact_person'] ?: null);
        $this->db->bind(':phone', $data['phone'] ?: null);
        $this->db->bind(':email', $data['email'] ?: null);
        $this->db->bind(':address', $data['address'] ?: null);
        $this->db->bind(':notes', $data['notes'] ?: null);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM suppliers WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
