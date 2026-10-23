<?php

declare(strict_types=1);

namespace Framework;

use PDO;

/**
 * Provides a lazily created PDO database connection.
 *
 * Stores the database connection settings and creates a configured
 * PDO instance on first access, reusing it for subsequent requests.
 */
class Database
{
    /**
     * Cached database connection.
     */
    private ?PDO $pdo = null;

    /**
     * Creates a database connection provider.
     *
     * Stores the credentials used when the PDO connection is first requested.
     *
     * @param string $host Database server host
     * @param string $databaseName Database name
     * @param string $user Database user name
     * @param string $password Database password
     */
    public function __construct(
        private string $host,
        private string $databaseName,
        private string $user,
        private string $password
    ) {}

    /**
     * Returns the shared PDO connection.
     *
     * Creates and configures the connection on first access, then returns
     * the same instance for the lifetime of this database provider.
     *
     * @return PDO Configured database connection
     *
     * @throws \PDOException If the database connection cannot be established
     */
    public function getConnection(): PDO
    {
        if ($this->pdo === null) {
            $dsn = "mysql:host={$this->host};dbname={$this->databaseName};charset=utf8mb4;port=3306";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false
            ];

            $pdo = new PDO($dsn, $this->user, $this->password, $options);

            $this->pdo = $pdo;
        }

        return $this->pdo;
    }
}
