<?php

declare(strict_types=1);

namespace Framework;

use PDO;

class Database
{
    public function __construct(
        private string $host,
        private string $databaseName,
        private string $user,
        private string $password
    ) {}


    public function getConnection(): PDO
    {
        $dsn = "mysql:host={$this->host};dbname={$this->databaseName};charset=utf8mb4;port=3306";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false
        ];

        $pdo = new PDO($dsn, $this->user, $this->password, $options);

        return $pdo;
    }
}
