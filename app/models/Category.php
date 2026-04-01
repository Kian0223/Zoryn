<?php
class Category extends Model
{
    public function getAll(): array
    {
        $this->db->query("SELECT * FROM categories ORDER BY category_name ASC");
        return $this->db->resultSet();
    }

    public function create(array $data): bool
    {
        $this->db->query("INSERT INTO categories (category_name) VALUES (:category_name)");
        $this->db->bind(':category_name', $data['category_name']);
        return $this->db->execute();
    }

    public function findById(int $id): array|false
    {
        $this->db->query("SELECT * FROM categories WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function update(int $id, array $data): bool
    {
        $this->db->query("UPDATE categories SET category_name = :category_name WHERE id = :id");
        $this->db->bind(':category_name', $data['category_name']);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM categories WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
