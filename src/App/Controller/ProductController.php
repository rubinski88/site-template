<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Product;

class ProductController
{
    public function index()
    {

        $products = (new Product)->getData();

        require ROOT_PATH . '/templates/product/index.php';
    }

    public function show()
    {
        require ROOT_PATH . 'templates/product/show.php';
    }
}
