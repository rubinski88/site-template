<?php

declare(strict_types=1);

namespace Framework\Routing;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Loads routes declared with controller method attributes.
 *
 * Reflects public controller methods and registers every discovered
 * Route attribute with the application router.
 */
final class AttributeLoader
{
    /**
     * Creates a route attribute loader.
     *
     * Stores the router that receives routes discovered on controllers.
     *
     * @param Router $router Router receiving discovered routes
     */
    public function __construct(
        private Router $router
    ) {}

    /**
     * Loads routes declared on public controller methods.
     *
     * Reflects the supplied controller class and registers each Route
     * attribute with its controller, action and middleware metadata.
     *
     * @param class-string $controllerClass Controller class to inspect
     *
     * @throws ReflectionException If the controller class cannot be reflected
     */
    public function load(string $controllerClass): void
    {
        $controller = new ReflectionClass($controllerClass);

        $methods = $controller->getMethods(
            ReflectionMethod::IS_PUBLIC,
        );

        foreach ($methods as $method) {
            $attributes = $method->getAttributes(Route::class);

            foreach ($attributes as $attribute) {
                /** @var Route $route */
                $route = $attribute->newInstance();

                $this->router->add(
                    $route->path,
                    [
                        'controller' => $controllerClass,
                        'action' => $method->getName(),
                        'middleware' => $route->middleware,
                    ],
                );
            }
        }
    }
}
