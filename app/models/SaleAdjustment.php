<?php
class SaleAdjustment extends Model
{
    public function create(array $data): bool
    {
        $this->db->query("
            INSERT INTO sale_adjustments (sale_id, adjustment_type, reason, amount, created_by, created_at)
            VALUES (:sale_id, :adjustment_type, :reason, :amount, :created_by, NOW())
        ");
        $this->db->bind(':sale_id', $data['sale_id']);
        $this->db->bind(':adjustment_type', $data['adjustment_type']);
        $this->db->bind(':reason', $data['reason'] ?: null);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':created_by', $data['created_by'] ?? null);
        return $this->db->execute();
    }

    public function getBySale(int $saleId): array
    {
        $this->db->query("
            SELECT sa.*, u.full_name
            FROM sale_adjustments sa
            LEFT JOIN users u ON u.id = sa.created_by
            WHERE sa.sale_id = :sale_id
            ORDER BY sa.created_at DESC, sa.id DESC
        ");
        $this->db->bind(':sale_id', $saleId);
        return $this->db->resultSet();
    }
}
