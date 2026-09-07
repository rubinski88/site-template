<?php

declare(strict_types=1);

namespace App\Model;

use PDO;

class Product
{
    public function getData(): array
    {
        $dsn = 'mysql:host=127.0.0.1;dbname=shop;charset=utf8mb4;port=3306';

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false
        ];

        $pdo = new PDO($dsn, 'shop_app', 'secret123', $options);

        $sql = 'SELECT * FROM products';

        $stmt = $pdo->query($sql);

        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $products;
    }
}
