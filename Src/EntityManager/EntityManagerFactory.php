<?php

namespace Emma\ORM\EntityManager;

use Emma\Common\Factory\ObjectFactory;
use Emma\Dbal\Connection\PDOConnection;
use Emma\Dbal\QueryBuilder\Expressions\WhereCompositeCondition;
use Emma\Dbal\QueryBuilder\Expressions\WhereCondition;
use Emma\Di\Container\ContainerManager;
use Emma\ORM\EntityManager\Hydrator\EntityHydrator;
use Emma\ORM\EntityManager\Interfaces\EntityHydratorInterface;
use Emma\ORM\EntityManager\Interfaces\EntityManagerInterface;
use Emma\ORM\EntityManager\Interfaces\EntityResolverInterface;
use Emma\ORM\EntityManager\Interfaces\RelationshipManagerInterface;
use Emma\ORM\EntityManager\RelationshipManager\RelationshipManager;
use Emma\ORM\EntityManager\Resolver\EntityResolver;

class EntityManagerFactory extends ObjectFactory implements EntityManagerInterface
{
    use ContainerManager;

    protected EntityManagerInterface $entityManager;

    private EntityHydratorInterface $entityHydrator;

    private EntityResolverInterface $entityResolver;

    private RelationshipManagerInterface $relationshipManager;

    /**
     * @param object|string $entity
     * @return $this
     */
    public function make(object|string $entity): static
    {
        $this->entityResolver = new EntityResolver($entity);
        $this->entityHydrator = new EntityHydrator($this->entityResolver);
        $this->relationshipManager = new RelationshipManager($this->entityResolver, $this->entityHydrator);
        $this->entityManager = new EntityManager($this->entityResolver, $this->entityHydrator, $this->relationshipManager);
        return clone $this;
    }

    public function persist(object &$entity): mixed
    {
        return $this->entityManager->persist($entity);
    }

    public function persistAll(array &$entities): bool
    {
        return $this->entityManager->persistAll($entities);
    }

    public function existsBy(WhereCondition|array|WhereCompositeCondition $criteria): bool
    {
        return $this->entityManager->existsBy($criteria);
    }

    public function count(WhereCondition|array|WhereCompositeCondition $criteria = []): int
    {
        return $this->entityManager->count($criteria);
    }

    public function countBy(WhereCondition|array|WhereCompositeCondition $criteria = []): int
    {
        return $this->entityManager->countBy($criteria);
    }

    public function sum(string $column, array $criteria = [], ?int $limit = null, ?int $offset = null): int
    {
        return $this->entityManager->sum($column, $criteria, $limit, $offset);
    }

    public function find(WhereCondition|array|WhereCompositeCondition $criteria, array $orderBy = [], ?int $limit = null, ?int $offset = null, ?int $endLimit = null, string $fetchMode = null): mixed
    {
        return $this->entityManager->find($criteria, $orderBy, $limit, $offset, $endLimit, $fetchMode);
    }

    public function findBy(WhereCondition|array|WhereCompositeCondition $criteria, array $orderBy = [], ?int $limit = null, ?int $offset = null, ?int $endLimit = null,): mixed
    {
        return $this->entityManager->findBy($criteria, $orderBy, $limit, $offset, $endLimit);
    }

    public function findOneBy(WhereCondition|array|WhereCompositeCondition $criteria, array $orderBy = []): mixed
    {
        return $this->entityManager->findOneBy($criteria, $orderBy);
    }

    public function findArrayBy(WhereCondition|array|WhereCompositeCondition $criteria, array $orderBy = [], ?int $limit = null, ?int $offset = null, ?int $endLimit = null): mixed
    {
        return $this->entityManager->findArrayBy($criteria, $orderBy, $limit, $offset, $endLimit);
    }

    public function findOneArrayBy(WhereCondition|array|WhereCompositeCondition $criteria, array $orderBy = []): mixed
    {
        return $this->entityManager->findOneArrayBy($criteria, $orderBy);
    }

    public function updateFields(array $fieldValues, WhereCompositeCondition|WhereCondition|array $criteria): mixed
    {
        return $this->entityManager->updateFields($fieldValues, $criteria);
    }

    public function deleteBy(WhereCondition|array|WhereCompositeCondition $criteria): mixed
    {
        return $this->entityManager->deleteBy($criteria);
    }

    public function deleteAll()
    {
        return $this->entityManager->deleteAll();
    }

    /**
     * @return EntityResolverInterface
     */
    public function getEntityResolver(): EntityResolverInterface
    {
        return $this->entityResolver;
    }

    /**
     * @return PDOConnection
     */
    public function getConnection(): PDOConnection
    {
        return $this->entityManager->getConnection();
    }

    /**
     * @param PDOConnection $connection
     * @return $this
     */
    public function setConnection(PDOConnection $connection): static
    {
        $this->entityManager->setConnection($connection);
        return $this;
    }


}