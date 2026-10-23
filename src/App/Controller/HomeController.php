<?php

declare(strict_types=1);

namespace App\Controller;

use Framework\Controller;
use Framework\Http\Response;
use Framework\Routing\Route;

/**
 * Handles requests for general application pages.
 *
 * Renders the home page and provides an example response with a custom
 * HTTP status code.
 */
class HomeController extends Controller
{
    /**
     * Displays the application home page.
     *
     * Renders the home template and returns it as the response body.
     *
     * @return Response Rendered home page response
     */
    #[Route('/')]
    public function index(): Response
    {
        return $this->view(
            'home/index',
            [
                'title' => 'Home'
            ]
        );
    }

    /**
     * Returns an example response with status code 451.
     *
     * Sets a custom status and plain-text body on the current response.
     *
     * @return Response Configured example response
     */
    #[Route(path: '/451')]
    public function responseCodeExample(): Response
    {
        $this->response->setStatusCode(451);

        $this->response->setBody('Unavailable for legal reason');

        return $this->response;
    }
}
