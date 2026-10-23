<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Product;
use Framework\Routing\Route;
use Framework\Controller;
use Framework\Http\Response;

class ProductController extends Controller
{
    public function __construct(
        private Product $model
    ) {}

    #[Route('/products')]
    public function index(): Response
    {

        $products = $this->model->getData();

        return $this->view(
            'product/index',
            [
                'title' => 'Products',
                'products' => $products
            ]
        );
    }

    #[Route('/products/{id:\d+}')]
    public function show(string $id): Response
    {

        return $this->view(
            'product/show',
            [
                'title' => 'Product ' . $id,
                'id' => $id
            ]
        );
    }
}
