<?php

declare(strict_types=1);

class ProductController
{
    public function index()
    {
        require ROOT_PATH . '/src/Model/Product.php';

        $products = (new \Product)->getData();

        require ROOT_PATH . '/templates/product/index.php';
    }
}
