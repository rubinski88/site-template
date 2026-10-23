<?php

declare(strict_types=1);

namespace App\Controller;

use App\Middleware\Redirect;
use App\Model\Product;
use Framework\Controller;
use Framework\Http\Response;
use Framework\NotFoundException;
use Framework\Routing\Route;

/**
 * Handles product resource requests.
 *
 * Coordinates product persistence with HTML responses for listing,
 * displaying, creating, editing and deleting products.
 */
class ProductController extends Controller
{
    /**
     * Creates a product controller.
     *
     * Stores the model used by product actions to access persisted data.
     *
     * @param Product $model Product model
     */
    public function __construct(
        private Product $model
    ) {}

    /**
     * Displays the product list.
     *
     * Loads all products and their total count, then renders the index page.
     *
     * @return Response Rendered product list response
     */
    #[Route('/products', methods: ['GET'])]
    public function index(): Response
    {
        $products = $this->model->findAll();

        return $this->view(
            'product/index',
            [
                'title' => 'Products',
                'products' => $products,
                'total' => $this->model->getTotal()
            ]
        );
    }

    /**
     * Returns a product by its identifier.
     *
     * Loads the requested product or reports that the resource does not
     * exist when the model returns no matching record.
     *
     * @param string $id Product identifier
     *
     * @return array<string, mixed> Product data
     *
     * @throws NotFoundException If the product does not exist
     */
    private function getProduct(string $id): array
    {
        $product = $this->model->find($id);

        if ($product === false) {
            throw new NotFoundException(
                'Product with id ' . $id . ' was not found'
            );
        }

        return $product;
    }

    /**
     * Displays a product.
     *
     * Loads the requested product and renders its detail page.
     *
     * @param string $id Product identifier
     *
     * @return Response Rendered product detail response
     *
     * @throws NotFoundException If the product does not exist
     */
    #[Route('/products/{id:\d+}', methods: ['GET'])]
    public function show(string $id): Response
    {
        $product = $this->getProduct($id);

        return $this->view(
            'product/show',
            [
                'title' => 'Product ' . $id,
                'product' => $product
            ]
        );
    }

    /**
     * Displays the product creation form.
     *
     * Renders an empty form for entering a new product.
     *
     * @return Response Rendered product creation form
     */
    #[Route('/products/new', methods: ['GET'])]
    public function new(): Response
    {
        return $this->view(
            'product/new',
            [
                'title' => 'New product'
            ]
        );
    }

    /**
     * Creates a product from submitted request data.
     *
     * Persists valid input and redirects to the new product, or renders the
     * creation form again with validation errors and submitted values.
     *
     * @return Response Redirect or rendered form response
     */
    #[Route('/products', methods: ['POST'])]
    public function create(): Response
    {
        $data = [
            'name' => $this->request->post['name'] ?? null,
            'description' => $this->request->post['description'] ?? null
        ];

        if ($this->model->insert($data)) {
            return $this->redirect("/products/{$this->model->getInsertId()}");
        } else {
            return $this->view(
                'product/new',
                [
                    'title' => 'New product',
                    'errors' => $this->model->getErrors(),
                    'product' => $data
                ]
            );
        }
    }

    /**
     * Displays the product editing form.
     *
     * Loads the requested product and renders a form populated with its data.
     *
     * @param string $id Product identifier
     *
     * @return Response Rendered product editing form
     *
     * @throws NotFoundException If the product does not exist
     */
    #[Route('/products/{id:\d+}/edit', methods: ['GET'], middleware: [Redirect::class])]
    public function edit(string $id): Response
    {
        $product = $this->getProduct($id);

        return $this->view(
            'product/edit',
            [
                'title' => 'Edit product ' . $id,
                'product' => $product
            ]
        );
    }

    /**
     * Updates a product from submitted request data.
     *
     * Persists valid changes and redirects to the product, or renders the
     * editing form again with validation errors and submitted values.
     *
     * @param string $id Product identifier
     *
     * @return Response Redirect or rendered form response
     *
     * @throws NotFoundException If the product does not exist
     */
    #[Route('/products/{id:\d+}', methods: ['PATCH'])]
    public function update(string $id): Response
    {
        $product = $this->getProduct($id);

        $product['name'] = $this->request->post['name'] ?? null;
        $product['description'] = $this->request->post['description'] ?? null;

        if ($this->model->update($id, $product)) {
            return $this->redirect("/products/{$id}");
        } else {
            return $this->view(
                'product/edit',
                [
                    'title' => 'Edit product',
                    'errors' => $this->model->getErrors(),
                    'product' => $product
                ]
            );
        }
    }

    /**
     * Deletes a product.
     *
     * Verifies that the product exists, deletes it and redirects to the
     * product list.
     *
     * @param string $id Product identifier
     *
     * @return Response Redirect response
     *
     * @throws NotFoundException If the product does not exist
     */
    #[Route('/products/{id:\d+}', methods: ['DELETE'])]
    public function delete(string $id): Response
    {
        $this->getProduct($id);

        $this->model->delete($id);

        return $this->redirect('/products');
    }

    // #[Route('/{title}/{id:\d+}/{page}')]
    // public function exampleShow(string $title, string $id, string $page)
    // {
    //     var_dump($title, $id, $page);
    // }
}
