<?php
class Database
{
    private PDO $pdo;
    private PDOStatement $stmt;

    public function __construct()
    {
        $db = require CONFIG_PATH . '/database.php';
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $db['host'], $db['dbname'], $db['charset']);

        $this->pdo = new PDO($dsn, $db['username'], $db['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    public function query(string $sql): void
    {
        $this->stmt = $this->pdo->prepare($sql);
    }

    public function bind(string $param, $value, $type = null): void
    {
        if ($type === null) {
            $type = match (true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                is_null($value) => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            };
        }

        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute(): bool
    {
        return $this->stmt->execute();
    }

    public function single(): array|false
    {
        $this->execute();
        return $this->stmt->fetch();
    }

    public function resultSet(): array
    {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    public function rowCount(): int
    {
        return $this->stmt->rowCount();
    }

    public function lastInsertId(): string|false
    {
        return $this->pdo->lastInsertId();
    }
}
