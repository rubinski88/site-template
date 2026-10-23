<?php

declare(strict_types=1);

namespace App\Model;

use Framework\Model;

use PDO;

class Product extends Model
{
    protected ?string $tableName = 'products';


    protected function validate(array $data): void
    {
        if (empty($data['name'])) {
            $this->addError('name', 'Empty name is not allowed');
        }
    }

    public function getTotal(): int
    {
        $sql = "SELECT COUNT(*) as records FROM {$this->tableName}";

        $conn = $this->database->getConnection();

        $stmt = $conn->query($sql);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) $row['records'];
    }
}
