<?php
class PromoRedemption extends Model
{
    public function create(array $data): bool
    {
        $this->db->query("
            INSERT INTO promo_redemptions (
                promo_id, sale_id, customer_id, discount_amount, created_at
            ) VALUES (
                :promo_id, :sale_id, :customer_id, :discount_amount, NOW()
            )
        ");
        $this->db->bind(':promo_id', $data['promo_id']);
        $this->db->bind(':sale_id', $data['sale_id'] ?: null);
        $this->db->bind(':customer_id', $data['customer_id'] ?: null);
        $this->db->bind(':discount_amount', $data['discount_amount']);
        return $this->db->execute();
    }
}
