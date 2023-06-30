<?php

namespace Emma\ORM\EntityManager\RelationshipManager;

use Emma\ORM\Attributes\Relationships\JoinColumn;
use Emma\ORM\Attributes\Relationships\JoinTable;
use Emma\ORM\Attributes\Relationships\RelationshipType;
use Emma\ORM\EntityManager\Helpers\RelationshipTypeHelper;
use Emma\ORM\EntityManager\Hydrator\EntityHydrator;
use Emma\ORM\EntityManager\Interfaces\EntityHydratorInterface;
use Emma\ORM\EntityManager\Interfaces\EntityResolverInterface;
use Emma\ORM\EntityManager\Interfaces\RelationshipManagerInterface;
use Emma\ORM\EntityManager\Resolver\EntityResolver;


class RelationshipManager implements RelationshipManagerInterface
{

    private bool $isFetchArray = false;

    public function __construct(
        protected EntityResolverInterface $entityResolver,
        protected EntityHydratorInterface $entityHydrator
    ) {

    }

    /**
     * @param array $result
     * @param $limit
     * @return mixed
     * @throws \ReflectionException
     */
    public function findEntityRelationship(array $result, $limit): mixed
    {
        $isSingleResult = (is_int($limit) && $limit == 1);

        $this->isFetchArray = $this->entityResolver->getEntityAttributeInstance()->fetchAsArray();

        if ($this->entityResolver->hasRelationship()) {
            $entityRelationships = $this->entityResolver->getEntityRelationships();
            if ($isSingleResult) {
                $object = $this->prepare($result, true);
                $reflectionObject = $this->entityResolver->getReflectionObject();
                return $this->resolveAllRelationships($entityRelationships, $result, $object, $reflectionObject, $limit);
            } else {
                foreach($result as $index => $row) {
                    $object = $this->prepare($row, true);
                    $reflectionObject = $this->entityResolver->getReflectionObject();
                    $result[$index] = $this->resolveAllRelationships($entityRelationships, $row, $object, $reflectionObject, $limit);
                }
                return $result;
            }
        }

        return $this->prepare($result, $isSingleResult);
    }

    /**
     * @param array $entityRelationships
     * @param array $row
     * @param object $object
     * @param \ReflectionObject $reflectionObject
     * @param int|null $limit
     * @return mixed
     * @throws \ReflectionException
     */
    protected function resolveAllRelationships(
        array $entityRelationships,
        array $row,
        object|array $resultObjectOrArray,
        \ReflectionObject $reflectionObject,
        ?int $limit): object|array
    {
        foreach($entityRelationships as $propertyName => $entityRelationship) {
            /** @var RelationshipType $relationshipType */
            $relationshipType = RelationshipTypeHelper::findRelationshipType($entityRelationship);
            if (empty($relationshipType)) {
                continue;
            }

            $propertyValue = $this->processRelationshipType(
                $relationshipType,
                RelationshipTypeHelper::findJoinColumn($entityRelationship),
                RelationshipTypeHelper::findJoinTable($entityRelationship),
                $row,
                RelationshipTypeHelper::isSingleResult($limit, basename($relationshipType::class))
            );

            if ($this->isFetchArray) {
                $resultObjectOrArray[$propertyName] = $propertyValue;
            } else {
                $property = $reflectionObject->getProperty($propertyName);
                $property->setValue($resultObjectOrArray, $propertyValue);
            }
        }
        return $resultObjectOrArray;
    }

    /**
     * @param RelationshipType $relationshipType
     * @param JoinColumn|null $joinColumn
     * @param JoinTable|null $joinTable
     * @param array $result
     * @param bool $isSingleResult
     * @return mixed
     * @throws \ReflectionException
     * @throws \Exception
     */
    protected function processRelationshipType(
        RelationshipType $relationshipType,
        ?JoinColumn $joinColumn,
        ?JoinTable $joinTable,
        array $result,
        bool $isSingleResult = false
    ): mixed
    {
        $prepareRelationship = new RelationshipQuery(
            $result,
            $this->entityResolver->getEntityAttributeInstance()->getFullTableName(),
            $relationshipType,
            $joinColumn ?? null,
            $joinTable ?? null,
            $isSingleResult
        );

        $data = $prepareRelationship->fetch();

        $entityResolver = new EntityResolver($relationshipType->targetEntity);
        $entityHydrator = new EntityHydrator($entityResolver);
        $entityManager  = new self($entityResolver, $entityHydrator);
        $entityManager->entityResolver->getEntityAttributeInstance()->setFetchAsArray($this->isFetchArray);
        return $entityManager->findEntityRelationship($data, $isSingleResult ? 1 : null);
    }

    /**
     * @param $result
     * @param $isSingleResult
     * @return mixed
     */
    public function prepare($result, $isSingleResult): mixed
    {
        if ($isSingleResult) {
            return $this->isFetchArray ? $result : $this->entityHydrator->hydrate($result);
        }
        if (!$this->isFetchArray) {
            foreach($result as $index => $row) {
                $result[$index] = $this->entityHydrator->hydrate($row);
            }
        }
        return $result;
    }
}