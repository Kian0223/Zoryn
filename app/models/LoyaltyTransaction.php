<?php
class LoyaltyTransaction extends Model
{
    public function create(array $data): bool
    {
        $this->db->query("
            INSERT INTO loyalty_transactions (
                customer_id, sale_id, transaction_type, points, peso_value, notes, created_by, created_at
            ) VALUES (
                :customer_id, :sale_id, :transaction_type, :points, :peso_value, :notes, :created_by, NOW()
            )
        ");
        $this->db->bind(':customer_id', $data['customer_id']);
        $this->db->bind(':sale_id', $data['sale_id'] ?: null);
        $this->db->bind(':transaction_type', $data['transaction_type']);
        $this->db->bind(':points', $data['points']);
        $this->db->bind(':peso_value', $data['peso_value'] ?? 0);
        $this->db->bind(':notes', $data['notes'] ?: null);
        $this->db->bind(':created_by', $data['created_by'] ?? null);
        return $this->db->execute();
    }

    public function getByCustomer(int $customerId): array
    {
        $this->db->query("
            SELECT lt.*, s.receipt_no
            FROM loyalty_transactions lt
            LEFT JOIN sales s ON s.id = lt.sale_id
            WHERE lt.customer_id = :customer_id
            ORDER BY lt.created_at DESC, lt.id DESC
        ");
        $this->db->bind(':customer_id', $customerId);
        return $this->db->resultSet();
    }
}
