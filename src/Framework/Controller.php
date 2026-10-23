<?php

declare(strict_types=1);

namespace Framework;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Templating\ViewInterface;

/**
 * Provides shared HTTP dependencies to application controllers.
 *
 * Stores the current request, response and template renderer assigned
 * by the dispatcher and provides response-building helper methods.
 */
abstract class Controller
{
    /**
     * Current HTTP request.
     */
    protected Request $request;

    /**
     * Current HTTP response.
     */
    protected Response $response;

    /**
     * Application template renderer.
     */
    protected ViewInterface $view;

    /**
     * Assigns the current HTTP request.
     *
     * Makes the request available to actions implemented by a concrete
     * controller.
     *
     * @param Request $request Incoming HTTP request
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * Assigns the current HTTP response.
     *
     * Makes the response available to actions and response-building helpers
     * implemented by the base controller.
     *
     * @param Response $response Outgoing HTTP response
     */
    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    /**
     * Assigns the application template renderer.
     *
     * Makes the renderer available to actions implemented by a concrete
     * controller.
     *
     * @param ViewInterface $view Application template renderer
     */
    public function setView(ViewInterface $view): void
    {
        $this->view = $view;
    }

    /**
     * Creates a response with rendered template content.
     *
     * Renders the selected template inside the specified layout, assigns
     * the generated content to the current response and returns it.
     *
     * @param string $template Template name without the file extension
     * @param array<string, mixed> $data Template data
     * @param string $layout Layout name without the file extension
     *
     * @return Response Response containing the rendered content
     */
    protected function view(
        string $template,
        array $data = [],
        string $layout = 'base'
    ): Response {
        $this->response->setBody(
            $this->view->render(
                $template,
                $data,
                $layout
            )
        );

        return $this->response;
    }

    /**
     * Creates a redirect response.
     *
     * Adds a Location header to the current response and returns it.
     *
     * @param string $url Redirect target URL
     *
     * @return Response Response containing the redirect header
     */
    protected function redirect(string $url): Response
    {
        $this->response->redirect($url);

        return $this->response;
    }
}
