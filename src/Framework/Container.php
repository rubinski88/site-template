<?php

declare(strict_types=1);

namespace Framework;

use Closure;
use ReflectionClass;
use ReflectionNamedType;
use LogicException;

/**
 * Resolves objects and their constructor dependencies.
 *
 * Uses explicitly registered factories when available and otherwise
 * creates objects by recursively resolving class-typed dependencies.
 */
class Container
{
    /**
     * Registered object factories.
     *
     * Each closure creates an object for the class name under which it is registered.
     *
     * @var array<class-string, Closure>
     */
    private array $registry = [];

    /**
     * Registers an object factory.
     *
     * The factory is stored by class name and invoked when that class is requested.
     *
     * @param class-string $className Fully qualified class name used as the registry key
     * @param Closure $factory Closure that creates an object of the registered class
     */
    public function set(string $className, Closure $factory): void
    {
        $this->registry[$className] = $factory;
    }

    /**
     * Resolves an object and its constructor dependencies.
     *
     * Uses a registered factory when available or recursively resolves
     * class-typed constructor parameters.
     *
     * @param class-string $className  Fully qualified class name of the requested object
     *
     * @return object Object created for the requested class
     *
     * @throws LogicException If a constructor parameter cannot be resolved automatically
     * @throws \ReflectionException If the requested class does not exist
     */
    public function get(string $className): object
    {
        if (array_key_exists($className, $this->registry)) {
            return $this->registry[$className]();
        }

        $reflector = new ReflectionClass($className);
        $constructor = $reflector->getConstructor();

        $dependencies = [];

        if ($constructor === null) {
            return new $className;
        }

        foreach ($constructor->getParameters() as $parameter) {
            $type  = $parameter->getType();

            if ($type === null) {
                throw new LogicException(
                    'Constructor parameter $' . $parameter->getName()
                        . ' in ' . $className
                        . ' has no type declaration'
                );
            }

            if (!($type instanceof ReflectionNamedType)) {
                throw new LogicException(
                    'Constructor parameter $' . $parameter->getName()
                        . ' in ' . $className
                        . ' must have a named type; '
                        . $type
                        . ' is not supported'
                );
            }

            if ($type->isBuiltIn()) {
                throw new LogicException(
                    'Constructor parameter $' . $parameter->getName()
                        . ' of type ' . $type->getName()
                        . ' in ' . $className
                        . ' cannot be resolved automatically'
                );
            }

            $dependencies[] = $this->get($type->getName());
        }

        $instance = new $className(...$dependencies);

        return $instance;
    }
}
