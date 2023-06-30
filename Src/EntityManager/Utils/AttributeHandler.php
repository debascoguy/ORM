<?php

namespace Emma\ORM\EntityManager\Utils;

use Emma\Di\Container\ContainerManager;
use Emma\ORM\Attributes\Entity\Entity;
use Emma\ORM\Attributes\Repository\Repository;
use ReflectionClass;

class AttributeHandler
{
    use ContainerManager;

    private function getName(object|string $objectOrClass): string
    {
        if (is_object($objectOrClass)) {
            return $objectOrClass instanceof ReflectionClass ? $objectOrClass->getName() : $objectOrClass::class;
        }
        return $objectOrClass;
    }

    /**
     * @throws \ReflectionException
     */
    public function getReflectionClass(object|string $objectOrClass): ReflectionClass
    {
        $name = $this->getName($objectOrClass);
        $name = $name . "::ReflectionClass";
        $container = $this->getContainer();
        if ($container->has($name)) {
            return $container->get($name);
        }
        $ref = $objectOrClass instanceof ReflectionClass ? $objectOrClass : new ReflectionClass($objectOrClass);
        $container->register($name, $ref);
        return $ref;
    }

    /**
     * @param object|string $objectOrClass
     * @return \ReflectionProperty[]|array
     * @throws \ReflectionException
     */
    public function getProperties(object|string $objectOrClass): array
    {
        return $this->getReflectionClass($objectOrClass)->getProperties();
    }

    /**
     * @param object|string $objectOrClass
     * @param $entityAttributes
     * @return bool
     * @throws \ReflectionException
     */
    public function isEntity(object|string $objectOrClass, &$entityAttributes): bool
    {
        $name = $this->getName($objectOrClass);
        $name = $name . "::ReflectionAttributes";
        $container = $this->getContainer();
        if ($container->has($name)) {
            $entityAttributes = $container->get($name);
        } else {
            $reflector = $this->getReflectionClass($objectOrClass);
            $entityAttributes = $reflector->getAttributes(Entity::class);
            $container->register($name, $entityAttributes);
        }
        return !empty($entityAttributes);
    }

    /**
     * @param object|string $objectOrClass
     * @param $repositoryAttributes
     * @return bool
     * @throws \ReflectionException
     */
    public function isRepository(object|string $objectOrClass, &$repositoryAttributes): bool
    {
        $name = $this->getName($objectOrClass);
        $name = $name . "::ReflectionAttributes";
        $container = $this->getContainer();
        if ($container->has($name)) {
            $repositoryAttributes = $container->get($name);
        } else {
            $reflector = $this->getReflectionClass($objectOrClass);
            $repositoryAttributes = $reflector->getAttributes(Repository::class);
            $container->register($name, $repositoryAttributes);
        }
        return !empty($repositoryAttributes);
    }

}