<?php

declare(strict_types=1);

namespace Cekta\DI;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class Reflection
{
    private $instantiable = [];
    private $dependencies = [];

    /**
     * @param string $name
     * @return array<string>
     * @internal
     */
    public function getDependencies(string $name): array
    {
        if (!array_key_exists($name, $this->dependencies)) {
            $this->load($name);
        }
        return $this->dependencies[$name];
    }

    /**
     * @param string $name
     * @return bool
     * @internal
     */
    public function isInstantiable(string $name): bool
    {
        if (!array_key_exists($name, $this->instantiable)) {
            $this->load($name);
        }
        return $this->instantiable[$name];
    }

    private function load(string $name): void
    {
        try {
            $class = new ReflectionClass($name);
            $this->instantiable[$name] = $class->isInstantiable();
            $this->dependencies[$name] = self::getMethodParameters($class->getConstructor());
        } catch (ReflectionException $exception) {
            $this->dependencies[$name] = [];
            $this->instantiable[$name] = false;
        }
    }

    private static function getMethodParameters(?ReflectionMethod $method): array
    {
        $result = [];
        if ($method !== null) {
            foreach ($method->getParameters() as $parameter) {
                $class = $parameter->getClass();
                $result[] = $class ? $class->name : $parameter->name;
            }
        }
        return $result;
    }
}