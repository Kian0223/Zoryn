<?php

class Viand extends Model
{
    public function getAll(): array
    {
        $this->db->query("SELECT 
                            v.id,
                            v.viand_name,
                            v.selling_price,
                            v.description,
                            v.created_at,
                            COALESCE(SUM(vi.quantity_needed * g.latest_cost), 0) AS total_ingredient_cost
                          FROM viands v
                          LEFT JOIN viand_ingredients vi ON vi.viand_id = v.id
                          LEFT JOIN groceries g ON g.id = vi.grocery_id
                          GROUP BY v.id, v.viand_name, v.selling_price, v.description, v.created_at
                          ORDER BY v.viand_name ASC");
        return $this->db->resultSet();
    }

    public function getAllWithCost(): array
    {
        return $this->getAll();
    }

    public function getTotalViands(): int
    {
        $this->db->query("SELECT COUNT(*) AS total FROM viands");
        $row = $this->db->single();
        return (int)($row['total'] ?? 0);
    }

    public function create(array $data): bool
    {
        $this->db->query("INSERT INTO viands (viand_name, selling_price, description)
                          VALUES (:viand_name, :selling_price, :description)");
        $this->db->bind(':viand_name', trim($data['viand_name'] ?? ''));
        $this->db->bind(':selling_price', (float)($data['selling_price'] ?? 0));
        $this->db->bind(':description', trim($data['description'] ?? '')) ?: null;
        return $this->db->execute();
    }

    public function getLastId(): int
    {
        return (int)$this->db->lastInsertId();
    }

    public function addIngredient(int $viandId, int $groceryId, float $quantityNeeded): bool
    {
        $this->db->query("INSERT INTO viand_ingredients (viand_id, grocery_id, quantity_needed)
                          VALUES (:viand_id, :grocery_id, :quantity_needed)");
        $this->db->bind(':viand_id', $viandId);
        $this->db->bind(':grocery_id', $groceryId);
        $this->db->bind(':quantity_needed', $quantityNeeded);
        return $this->db->execute();
    }

    public function getIngredientsByViand(int $viandId): array
    {
        $this->db->query("SELECT 
                            vi.id,
                            vi.viand_id,
                            vi.grocery_id,
                            vi.quantity_needed,
                            g.grocery_name,
                            g.unit,
                            g.latest_cost,
                            (vi.quantity_needed * g.latest_cost) AS subtotal
                          FROM viand_ingredients vi
                          INNER JOIN groceries g ON g.id = vi.grocery_id
                          WHERE vi.viand_id = :viand_id
                          ORDER BY g.grocery_name ASC");
        $this->db->bind(':viand_id', $viandId);
        return $this->db->resultSet();
    }

    public function getCostingSummary(int $viandId): array|false
    {
        $this->db->query("SELECT 
                            v.id,
                            v.viand_name,
                            v.selling_price,
                            v.description,
                            COALESCE(SUM(vi.quantity_needed * g.latest_cost), 0) AS total_cost
                          FROM viands v
                          LEFT JOIN viand_ingredients vi ON vi.viand_id = v.id
                          LEFT JOIN groceries g ON g.id = vi.grocery_id
                          WHERE v.id = :viand_id
                          GROUP BY v.id, v.viand_name, v.selling_price, v.description
                          LIMIT 1");
        $this->db->bind(':viand_id', $viandId);
        $row = $this->db->single();

        if (!$row) {
            return false;
        }

        $row['profit_estimate'] = (float)$row['selling_price'] - (float)$row['total_cost'];
        return $row;
    }

    public function deleteIngredientsByViand(int $viandId): bool
    {
        $this->db->query("DELETE FROM viand_ingredients WHERE viand_id = :viand_id");
        $this->db->bind(':viand_id', $viandId);
        return $this->db->execute();
    }

    public function delete(int $id): bool
    {
        $this->deleteIngredientsByViand($id);
        $this->db->query("DELETE FROM viands WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
