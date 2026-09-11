<?php

declare(strict_types=1);

namespace Framework;

use Framework\Routing\Router;
use ReflectionMethod;
use LogicException;
use Framework\Routing\RouteNotFoundException;


class Dispatcher
{
    public function __construct(
        private Router $router,
        private Container $container
    ) {}

    /**
     * Dispatches a request path to its registered controller action
     *
     * Resolves the matching route, validates its handler
     * and invokes the action with captured path parameters
     *
     * @param string $path Request path to dispatch
     *
     * @throws LogicException If the controller class does not exist or the action is not callable
     */
    public function handle(string $path): void
    {
        $params = $this->router->match($path);

        if ($params === false) {
            throw new RouteNotFoundException(
                'No route matches the request path ' . $path
            );
        }

        $controllerClass = $params['controller'];
        $action = $params['action'];

        if (!class_exists($controllerClass)) {
            throw new LogicException(
                'Controller class ' . $controllerClass . ' does not exist'
            );
        }

        $controller = $this->container->get($controllerClass);

        if (!is_callable([$controller, $action])) {
            throw new LogicException(
                'Controller action '
                    . $controllerClass
                    . '::'
                    . $action
                    . ' is not callable'
            );
        }

        $args = $this->getActionArguments($controllerClass, $action, $params);

        $controller->$action(...$args);
    }

    /**
     * Builds arguments for a controller action
     *
     * Uses reflection to read action parameter names
     * and maps them to matched route parameter values
     *
     * @param string $controllerClass Fully qualified controller class name
     * @param string $action Controller action method name
     * @param array<string, mixed> $params Matched route parameters
     *
     * @return array<string, mixed> Arguments indexed by action parameter names
     */
    private function getActionArguments(
        string $controllerClass,
        string $action,
        array $params
    ): array {
        $args = [];

        $method = new ReflectionMethod($controllerClass, $action);

        foreach ($method->getParameters() as $parameter) {
            $name = $parameter->getName();

            $args[$name] = $params[$name];
        }

        return $args;
    }
}
