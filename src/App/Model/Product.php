<?php

declare(strict_types=1);

namespace App\Model;

use Framework\Database;

class Product
{
    public function __construct(
        private Database $database
    ) {}

    public function getData(): array
    {
        $pdo = $this->database->getConnection();

        $sql = 'SELECT * FROM products';

        $stmt = $pdo->query($sql);

        $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $products;
    }
}
