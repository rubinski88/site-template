<?php

declare(strict_types=1);

namespace Framework;

use InvalidArgumentException;
use PDO;

/**
 * Provides shared persistence behavior for table-backed models.
 *
 * Resolves the database table name, stores validation errors and implements
 * common create, read, update and delete operations through PDO.
 */
abstract class Model
{
    /**
     * Validates data before it is persisted.
     *
     * Concrete models define their own rules and record validation failures
     * through addError().
     *
     * @param array<string, mixed> $data Data to validate
     */
    abstract protected function validate(array $data): void;

    /**
     * Explicit database table name.
     *
     * When set to null, the model derives a name from its class name.
     */
    protected ?string $tableName;

    /**
     * Returns the database table name for the model.
     *
     * Uses an explicitly configured name when available or derives a
     * lowercase plural name from the concrete model class.
     *
     * @return string Resolved database table name
     */
    private function getTableName(): string
    {
        if ($this->tableName !== null) {
            return $this->tableName;
        }

        $parts = explode('\\', $this::class);

        return array_pop($parts)
        |> strtolower(...)
        |> (fn(string $name): string => $name . 's');
    }

    /**
     * Creates a model with its database dependency.
     *
     * Stores the database provider used by all persistence operations.
     *
     * @param Database $database Application database provider
     */
    public function __construct(
        protected Database $database
    ) {}

    /**
     * Validation errors indexed by field name.
     *
     * @var array<string, string>
     */
    protected array $errors = [];

    /**
     * Records a validation error for a field.
     *
     * Replaces an existing error when the same field is reported again.
     *
     * @param string $field Invalid field name
     * @param string $message Validation error message
     */
    protected function addError(string $field, string $message): void
    {
        $this->errors[$field]  = $message;
    }

    /**
     * Returns the current validation errors.
     *
     * Exposes errors collected by the concrete model during validation.
     *
     * @return array<string, string> Validation errors indexed by field name
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Returns every record from the model table.
     *
     * Fetches each row as an associative array using the resolved table name.
     *
     * @return list<array<string, mixed>> Table records
     *
     * @throws \PDOException If the database query fails
     */
    public function findAll(): array
    {
        $pdo = $this->database->getConnection();

        $sql = 'SELECT * FROM ' . $this->getTableName();

        $stmt = $pdo->query($sql);

        $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $products;
    }

    /**
     * Finds a record by its identifier.
     *
     * Queries the resolved model table and returns the matching associative
     * row or false when no record exists.
     *
     * @param string $id Record identifier
     *
     * @return array<string, mixed>|false Matching record or false
     *
     * @throws \PDOException If the database query fails
     */
    public function find(string $id): array|false
    {
        $conn = $this->database->getConnection();

        $sql = 'SELECT *
                FROM ' . $this->getTableName() . '
                WHERE id = :id';

        $stmt = $conn->prepare($sql);

        $stmt->bindValue('id', $id, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Inserts a record into the model table.
     *
     * Validates the supplied data, builds an INSERT statement from its keys
     * and binds each value using the corresponding PDO parameter type.
     *
     * @param array<string, mixed> $data Record data indexed by column name
     *
     * @return bool True on success or false when validation fails
     *
     * @throws InvalidArgumentException If an empty valid data set is supplied
     * @throws \PDOException If the database operation fails
     */
    public function insert(array $data): bool
    {
        $this->validate($data);

        if (!empty($this->errors)) {
            return false;
        }

        if ($data === []) {
            throw new InvalidArgumentException(
                'Cannot insert an empty data set'
            );
        }



        $columnNames = array_keys($data);
        $columns = implode(', ', $columnNames);

        $placeholderList = array_fill(0, count($data), '?');
        $placeholders = implode(', ', $placeholderList);

        $sql = "INSERT INTO {$this->getTableName()} ({$columns})
                VALUES ({$placeholders})";

        $conn = $this->database->getConnection();

        $stmt = $conn->prepare($sql);

        $i = 1;

        foreach ($data as $value) {

            $type = match (gettype($value)) {
                'boolean' => PDO::PARAM_BOOL,
                'integer' => PDO::PARAM_INT,
                'NULL' => PDO::PARAM_NULL,
                default => PDO::PARAM_STR
            };

            $stmt->bindValue($i++, $value, $type);
        }


        return $stmt->execute();
    }

    /**
     * Updates a record in the model table.
     *
     * Validates the supplied data, removes any identifier field from the
     * assignments and binds the record identifier in the WHERE clause.
     *
     * @param string $id Record identifier
     * @param array<string, mixed> $data Updated data indexed by column name
     *
     * @return bool True on success or false when validation fails
     *
     * @throws \PDOException If the database operation fails
     */
    public function update(string $id, array $data): bool
    {
        $this->validate($data);

        if (!empty($this->errors)) {
            return false;
        }

        $sql = "UPDATE {$this->getTableName()} ";

        unset($data['id']);

        $assignments = array_keys($data);

        array_walk($assignments, function (&$value) {
            $value = "{$value} = ?";
        });

        $sql .= ' SET ' . implode(', ', $assignments);

        $sql .= ' WHERE id = ?';

        $conn = $this->database->getConnection();

        $stmt = $conn->prepare($sql);

        $i = 1;

        foreach ($data as $value) {

            $type = match (gettype($value)) {
                'boolean' => PDO::PARAM_BOOL,
                'integer' => PDO::PARAM_INT,
                'NULL' => PDO::PARAM_NULL,
                default => PDO::PARAM_STR
            };

            $stmt->bindValue($i++, $value, $type);
        }

        $stmt->bindValue($i, $id, PDO::PARAM_INT);


        return $stmt->execute();
    }

    /**
     * Returns the identifier generated by the last insert operation.
     *
     * Reads the value from the model's shared PDO connection.
     *
     * @return string Last inserted identifier
     */
    public function getInsertId(): string
    {
        $conn = $this->database->getConnection();

        return $conn->lastInsertId();
    }

    /**
     * Deletes a record from the model table.
     *
     * Executes a parameterized DELETE statement for the supplied identifier.
     *
     * @param string $id Record identifier
     *
     * @return bool True on successful execution
     *
     * @throws \PDOException If the database operation fails
     */
    public function delete(string $id): bool
    {
        $sql = "
                DELETE FROM {$this->getTableName()}
                WHERE id = :id
";

        $conn = $this->database->getConnection();

        $stmt = $conn->prepare($sql);

        $stmt->bindValue('id', $id, PDO::PARAM_INT);


        return $stmt->execute();
    }
}
