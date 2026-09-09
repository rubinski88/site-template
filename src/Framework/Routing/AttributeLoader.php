<?php

declare(strict_types=1);

namespace Framework\Routing;

use ReflectionClass;
use ReflectionMethod;
use ReflectionException;

final class AttributeLoader
{

    public function __construct(
        private Router $router
    ) {}

    /**
     * Loads routes declared as attributes on a controller
     *
     * Reflects public controller methods and registers each discovered route
     *
     * @param class-string $controllerClass Controller class to inspect
     *
     * @throws ReflectionException If the controller class does not exist
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
                    ],
                );
            }
        }
    }
}
