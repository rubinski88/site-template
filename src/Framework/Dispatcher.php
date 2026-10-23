<?php

declare(strict_types=1);

namespace Framework;

use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Routing\MethodNotAllowedException;
use Framework\Routing\RouteNotFoundException;
use Framework\Routing\Router;
use Framework\Templating\ViewInterface;
use LogicException;
use ReflectionMethod;
use UnexpectedValueException;
use Uri\Rfc3986\Uri;

/**
 * Dispatches incoming HTTP requests to controller actions.
 *
 * Coordinates request path extraction, route matching,
 * controller resolution, dependency assignment,
 * action argument mapping and controller invocation.
 */
class Dispatcher
{
    /**
     * Creates a dispatcher with its required collaborators.
     *
     * Stores the router used to match incoming requests and
     * the container used to resolve application components.
     *
     * @param Router $router Application router
     * @param Container $container Dependency injection container
     */
    public function __construct(
        private Router $router,
        private Container $container,
    ) {}

    /**
     * Dispatches an HTTP request to a registered controller action.
     *
     * Matches the request, resolves the controller and route middleware,
     * then passes the request through the middleware pipeline.
     *
     * @param Request $request Incoming HTTP request
     *
     * @return Response Produced HTTP response
     *
     * @throws UnexpectedValueException If the request URI is malformed
     * @throws RouteNotFoundException If no route matches the request
     * @throws MethodNotAllowedException If the method is not allowed
     * @throws LogicException If a route component cannot be resolved
     * @throws \ReflectionException If a dependency cannot be reflected
     */
    public function handle(Request $request): Response
    {
        $path = $this->getPath($request->uri);

        $params = $this->router->match(
            $path,
            $request->method,
        );

        if ($params === false) {
            throw new RouteNotFoundException(
                'No route matches the request path ' . $path
            );
        }

        $controllerClass = $params['controller'];
        $action = $params['action'];

        if (!class_exists($controllerClass)) {
            throw new LogicException(
                'Controller class '
                    . $controllerClass
                    . ' does not exist'
            );
        }

        $controller = $this->container->get(
            $controllerClass
        );

        if (!$controller instanceof Controller) {
            throw new LogicException(
                $controllerClass
                    . ' must extend '
                    . Controller::class
            );
        }

        $controller->setResponse(
            $this->container->get(Response::class)
        );

        $controller->setView(
            $this->container->get(
                ViewInterface::class
            )
        );

        if (!is_callable([$controller, $action])) {
            throw new LogicException(
                'Controller action '
                    . $controllerClass
                    . '::'
                    . $action
                    . ' is not callable'
            );
        }

        $args = $this->getActionArguments(
            $controllerClass,
            $action,
            $params,
        );

        $controllerRequestHandler = new ControllerRequestHandler(
            $controller,
            $action,
            $args,
        );

        $middleware = $this->resolveMiddleware(
            $params['middleware'] ?? [],
        );

        $middlewareRequestHandler = new MiddlewareRequestHandler(
            $middleware,
            $controllerRequestHandler,
        );

        return $middlewareRequestHandler->handle($request);
    }

    /**
     * Builds arguments for a controller action.
     *
     * Reflects the controller method and maps every action parameter
     * to the value captured under the same name by the router.
     *
     * @param class-string $controllerClass Controller class name
     * @param string $action Controller action name
     * @param array<string, mixed> $params Matched route parameters
     *
     * @return array<string, mixed> Controller action arguments
     *
     * @throws \ReflectionException If the controller action cannot be reflected
     */
    private function getActionArguments(
        string $controllerClass,
        string $action,
        array $params
    ): array {
        $args = [];

        $method = new ReflectionMethod(
            $controllerClass,
            $action,
        );

        foreach ($method->getParameters() as $parameter) {
            $name = $parameter->getName();

            $args[$name] = $params[$name];
        }

        return $args;
    }


    /**
     * Extracts the normalized path from a request URI.
     *
     * Parses the supplied URI according to RFC 3986 and rejects
     * malformed values or URIs without a path component.
     *
     * @param string $requestUri Raw request URI
     *
     * @return string Normalized request path
     *
     * @throws UnexpectedValueException If the URI is malformed or has no path
     */
    private function getPath(string $requestUri): string
    {
        $parsedUri = Uri::parse($requestUri);

        if (
            $parsedUri === null
            || $parsedUri->getRawPath() === ''
        ) {
            throw new UnexpectedValueException(
                'Malformed URL: ' . $requestUri
            );
        }

        return $parsedUri->getPath();
    }



    /**
     * Resolves route middleware through the container.
     *
     * Creates middleware objects in declaration order and verifies
     * that each object implements the middleware contract.
     *
     * @param list<class-string<MiddlewareInterface>> $middlewareClasses Middleware classes
     *
     * @return list<MiddlewareInterface> Resolved middleware objects
     *
     * @throws LogicException If a class does not implement MiddlewareInterface
     * @throws \ReflectionException If a middleware cannot be reflected
     */
    private function resolveMiddleware(
        array $middlewareClasses
    ): array {
        $middleware = [];

        foreach ($middlewareClasses as $middlewareClass) {
            $instance = $this->container->get(
                $middlewareClass
            );

            if (!$instance instanceof MiddlewareInterface) {
                throw new LogicException(
                    $middlewareClass
                        . ' must implement '
                        . MiddlewareInterface::class
                );
            }

            $middleware[] = $instance;
        }

        return $middleware;
    }
}
