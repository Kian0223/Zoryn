<?php
class User extends Model
{
    public function findByUsername(string $username): array|false
    {
        $this->db->query('SELECT * FROM users WHERE username = :username LIMIT 1');
        $this->db->bind(':username', $username);
        return $this->db->single();
    }

    public function getAll(): array
    {
        $this->db->query('SELECT id, full_name, username, role, created_at FROM users ORDER BY id DESC');
        return $this->db->resultSet();
    }

    public function getTotalUsers(): int
    {
        $this->db->query('SELECT COUNT(*) AS total FROM users');
        $row = $this->db->single();
        return (int)($row['total'] ?? 0);
    }

    public function create(array $data): bool
    {
        $this->db->query('INSERT INTO users (full_name, username, password, role) VALUES (:full_name, :username, :password, :role)');
        $this->db->bind(':full_name', $data['full_name']);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':password', password_hash($data['password'], PASSWORD_DEFAULT));
        $this->db->bind(':role', $data['role']);
        return $this->db->execute();
    }

    public function update(int $id, array $data): bool
    {
        if (!empty($data['password'])) {
            $this->db->query('UPDATE users SET full_name = :full_name, username = :username, password = :password, role = :role WHERE id = :id');
            $this->db->bind(':password', password_hash($data['password'], PASSWORD_DEFAULT));
        } else {
            $this->db->query('UPDATE users SET full_name = :full_name, username = :username, role = :role WHERE id = :id');
        }
        $this->db->bind(':full_name', $data['full_name']);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':role', $data['role']);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function delete(int $id): bool
    {
        $this->db->query('DELETE FROM users WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
