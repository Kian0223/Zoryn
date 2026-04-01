<?php
class Customer extends Model
{
    public function getAll(): array
    {
        $this->db->query("SELECT * FROM customers ORDER BY full_name ASC");
        $rows = $this->db->resultSet();
        return is_array($rows) ? $rows : [];
    }

    public function findById(int $id): array|false|object
    {
        $this->db->query("SELECT * FROM customers WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getSummary(): array
    {
        $this->db->query("
            SELECT
                COUNT(*) AS total_customers,
                COALESCE(SUM(total_points), 0) AS total_points,
                COALESCE(SUM(total_spent), 0) AS total_spent,
                COALESCE(SUM(visit_count), 0) AS total_visits
            FROM customers
        ");

        $row = $this->db->single();

        return [
            'total_customers' => (int)($row->total_customers ?? 0),
            'total_points' => (float)($row->total_points ?? 0),
            'total_spent' => (float)($row->total_spent ?? 0),
            'total_visits' => (int)($row->total_visits ?? 0),
        ];
    }

    public function getTopRepeatCustomers(int $limit = 20): array
    {
        $limit = max(1, (int)$limit);

        $this->db->query("
            SELECT
                id,
                customer_code,
                full_name,
                phone,
                email,
                total_points,
                total_spent,
                visit_count,
                birthdate
            FROM customers
            ORDER BY visit_count DESC, total_spent DESC, full_name ASC
            LIMIT {$limit}
        ");

        $rows = $this->db->resultSet();
        return is_array($rows) ? $rows : [];
    }

    public function applySale(int $customerId, float $saleAmount = 0, float $pointsEarned = 0): bool
    {
        $saleAmount = (float)$saleAmount;
        $pointsEarned = (float)$pointsEarned;

        $this->db->query("
            UPDATE customers
            SET
                total_spent = COALESCE(total_spent, 0) + :sale_amount,
                total_points = COALESCE(total_points, 0) + :points_earned,
                visit_count = COALESCE(visit_count, 0) + 1
            WHERE id = :id
        ");
        $this->db->bind(':sale_amount', $saleAmount);
        $this->db->bind(':points_earned', $pointsEarned);
        $this->db->bind(':id', $customerId);

        return $this->db->execute();
    }

    public function redeemPoints(int $customerId, float $points): bool
    {
        $points = max(0, (float)$points);

        $this->db->query("
            UPDATE customers
            SET total_points = GREATEST(COALESCE(total_points, 0) - :points, 0)
            WHERE id = :id
        ");
        $this->db->bind(':points', $points);
        $this->db->bind(':id', $customerId);

        return $this->db->execute();
    }

    public function create(array $data): bool
    {
        $this->db->query("
            INSERT INTO customers (
                full_name, phone, email, address, birthdate,
                customer_code, total_points, total_spent, visit_count
            ) VALUES (
                :full_name, :phone, :email, :address, :birthdate,
                :customer_code, 0, 0, 0
            )
        ");
        $this->db->bind(':full_name', trim((string)($data['full_name'] ?? '')));
        $this->db->bind(':phone', trim((string)($data['phone'] ?? '')) ?: null);
        $this->db->bind(':email', trim((string)($data['email'] ?? '')) ?: null);
        $this->db->bind(':address', trim((string)($data['address'] ?? '')) ?: null);
        $this->db->bind(':birthdate', trim((string)($data['birthdate'] ?? '')) ?: null);
        $this->db->bind(':customer_code', 'CUST-' . date('YmdHis') . '-' . rand(100, 999));

        return $this->db->execute();
    }

    public function update(int $id, array $data): bool
    {
        $this->db->query("
            UPDATE customers
            SET
                full_name = :full_name,
                phone = :phone,
                email = :email,
                address = :address,
                birthdate = :birthdate
            WHERE id = :id
        ");
        $this->db->bind(':full_name', trim((string)($data['full_name'] ?? '')));
        $this->db->bind(':phone', trim((string)($data['phone'] ?? '')) ?: null);
        $this->db->bind(':email', trim((string)($data['email'] ?? '')) ?: null);
        $this->db->bind(':address', trim((string)($data['address'] ?? '')) ?: null);
        $this->db->bind(':birthdate', trim((string)($data['birthdate'] ?? '')) ?: null);
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM customers WHERE id = :id");
        $this->db->bind(':id', $id);

        return $this->db->execute();
    }

    public function getBirthdayCustomersThisMonth(): array
    {
        $this->db->query("
            SELECT *
            FROM customers
            WHERE birthdate IS NOT NULL
              AND MONTH(birthdate) = MONTH(CURDATE())
            ORDER BY DAY(birthdate) ASC, full_name ASC
        ");
        $rows = $this->db->resultSet();
        return is_array($rows) ? $rows : [];
    }

    public function getSmsExportRows(): array
    {
        $this->db->query("
            SELECT customer_code, full_name, phone, email, total_points, total_spent, visit_count,
                   DATE_FORMAT(birthdate, '%Y-%m-%d') AS birthdate
            FROM customers
            WHERE phone IS NOT NULL AND phone <> ''
            ORDER BY full_name ASC
        ");
        $rows = $this->db->resultSet();
        return is_array($rows) ? $rows : [];
    }
}