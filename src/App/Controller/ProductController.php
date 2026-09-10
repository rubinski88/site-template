<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Product;
use Framework\Routing\Route;
use Framework\View;

class ProductController
{
    public function __construct(
        private View $view,
        private Product $model
    ) {}

    #[Route('/products')]
    public function index()
    {

        $products = $this->model->getData();

        echo $this->view->render(
            'product/index',
            [
                'products' => $products
            ]
        );
    }

    #[Route('/products/{id:\d+}')]
    public function show(string $id)
    {

        echo $this->view->render(
            'product/show',
            [
                'id' => $id
            ]
        );
    }

    #[Route('/{title}/{id:\d+}/{page}')]
    public function exampleShow(string $title, string $id, string $page)
    {
        var_dump($title, $id, $page);
    }
}
