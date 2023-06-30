<?php

namespace Emma\ORM\EntityManager\Hydrator;

use Emma\ORM\EntityManager\Interfaces\EntityHydratorInterface;
use Emma\ORM\EntityManager\Interfaces\EntityResolverInterface;
use Emma\ORM\EntityManager\Utils\PropertyInfo;
use Emma\Validation\Resolver\DataTypeConverterResolver;

class EntityHydrator implements EntityHydratorInterface
{
    use DataTypeConverterResolver;

    public function __construct(protected EntityResolverInterface $entityResolver)
    {

    }

    /**
     * @param object $entity
     * @param array $primaryKeys
     * @param int|string $value
     * @return object
     * @throws \ReflectionException
     */
    public function setPrimaryKey(object $entity, array $primaryKeys, int|string $value): object
    {
        /** @var PropertyInfo[] $allResolvedFields */
        $allResolvedFields = $this->entityResolver->getAllResolvedFields();
        $reflectionObject = $this->entityResolver->getReflectionObject();
        foreach($primaryKeys as $columnName => $_val) {
            $field = $allResolvedFields[$columnName];
            $property = $reflectionObject->getProperty($field->getPropertyName());
            $property->setValue($entity, $value);
        }
        return $entity;
    }

    /**
     * @param array $row
     * @return object
     * @throws \ReflectionException
     */
    public function hydrate(array $row): object
    {
        $entity = $this->entityResolver->getEntity();
        $reflectionObject = $this->entityResolver->getReflectionObject();
        /** @var PropertyInfo[] $allResolvedFields */
        $allResolvedFields = $this->entityResolver->getAllResolvedFields();
        $rowEntity = clone $entity;
        foreach($row as $columName => $value) {
            if (isset($allResolvedFields[$columName])) {
                $field = $allResolvedFields[$columName];
                $property = $reflectionObject->getProperty($field->getPropertyName());
                $value = $this->resolveOutput($property, $value, $row);
                $property->setValue($rowEntity, $value);
            }
        }
        return $rowEntity;
    }

}