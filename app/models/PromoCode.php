<?php
class PromoCode extends Model
{
    public function getAll(): array
    {
        $this->db->query("
            SELECT *,
                   CASE
                       WHEN is_active = 0 THEN 'inactive'
                       WHEN start_date IS NOT NULL AND start_date > CURDATE() THEN 'scheduled'
                       WHEN end_date IS NOT NULL AND end_date < CURDATE() THEN 'expired'
                       WHEN usage_limit IS NOT NULL AND times_used >= usage_limit THEN 'used_up'
                       ELSE 'active'
                   END AS promo_status
            FROM promo_codes
            ORDER BY id DESC
        ");
        return $this->db->resultSet();
    }

    public function findByCode(string $code): array|false
    {
        $this->db->query("SELECT * FROM promo_codes WHERE code = :code LIMIT 1");
        $this->db->bind(':code', $code);
        return $this->db->single();
    }

    public function create(array $data): bool
    {
        $this->db->query("
            INSERT INTO promo_codes (
                code, promo_name, discount_type, discount_value, min_spend,
                start_date, end_date, usage_limit, times_used, is_active, notes, created_at
            ) VALUES (
                :code, :promo_name, :discount_type, :discount_value, :min_spend,
                :start_date, :end_date, :usage_limit, 0, :is_active, :notes, NOW()
            )
        ");
        $this->db->bind(':code', strtoupper(trim($data['code'])));
        $this->db->bind(':promo_name', $data['promo_name']);
        $this->db->bind(':discount_type', $data['discount_type']);
        $this->db->bind(':discount_value', $data['discount_value']);
        $this->db->bind(':min_spend', $data['min_spend']);
        $this->db->bind(':start_date', $data['start_date'] ?: null);
        $this->db->bind(':end_date', $data['end_date'] ?: null);
        $this->db->bind(':usage_limit', $data['usage_limit'] !== '' ? $data['usage_limit'] : null);
        $this->db->bind(':is_active', !empty($data['is_active']) ? 1 : 0);
        $this->db->bind(':notes', $data['notes'] ?: null);
        return $this->db->execute();
    }

    public function update(int $id, array $data): bool
    {
        $this->db->query("
            UPDATE promo_codes
            SET code = :code,
                promo_name = :promo_name,
                discount_type = :discount_type,
                discount_value = :discount_value,
                min_spend = :min_spend,
                start_date = :start_date,
                end_date = :end_date,
                usage_limit = :usage_limit,
                is_active = :is_active,
                notes = :notes
            WHERE id = :id
        ");
        $this->db->bind(':code', strtoupper(trim($data['code'])));
        $this->db->bind(':promo_name', $data['promo_name']);
        $this->db->bind(':discount_type', $data['discount_type']);
        $this->db->bind(':discount_value', $data['discount_value']);
        $this->db->bind(':min_spend', $data['min_spend']);
        $this->db->bind(':start_date', $data['start_date'] ?: null);
        $this->db->bind(':end_date', $data['end_date'] ?: null);
        $this->db->bind(':usage_limit', $data['usage_limit'] !== '' ? $data['usage_limit'] : null);
        $this->db->bind(':is_active', !empty($data['is_active']) ? 1 : 0);
        $this->db->bind(':notes', $data['notes'] ?: null);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM promo_codes WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function incrementUsage(int $id): bool
    {
        $this->db->query("UPDATE promo_codes SET times_used = times_used + 1 WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function computeDiscount(array $promo, float $gross): float
    {
        if ((int)($promo['is_active'] ?? 0) !== 1) return 0;
        if (!empty($promo['start_date']) && $promo['start_date'] > date('Y-m-d')) return 0;
        if (!empty($promo['end_date']) && $promo['end_date'] < date('Y-m-d')) return 0;
        if (!empty($promo['usage_limit']) && (int)$promo['times_used'] >= (int)$promo['usage_limit']) return 0;
        if ($gross < (float)($promo['min_spend'] ?? 0)) return 0;

        if (($promo['discount_type'] ?? 'fixed') === 'percent') {
            return round($gross * ((float)$promo['discount_value'] / 100), 2);
        }
        return min($gross, (float)$promo['discount_value']);
    }
}
