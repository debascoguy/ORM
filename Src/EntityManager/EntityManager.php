<?php

namespace Emma\ORM\EntityManager;

use Emma\Dbal\Connection\PDOConnection;
use Emma\Dbal\QueryBuilder\Constants\FetchMode;
use Emma\Dbal\QueryBuilder\Constants\QueryType;
use Emma\Dbal\QueryBuilder\Expressions\WhereCompositeCondition;
use Emma\Dbal\QueryBuilder\Expressions\WhereCondition;
use Emma\Dbal\QueryBuilder\Query;
use Emma\Dbal\QueryBuilder\Services\CriteriaHandler;
use Emma\Dbal\QueryBuilder\Services\Select;
use Emma\ORM\Connection\Connection;
use Emma\ORM\EntityManager\Hydrator\EntityHydrator;
use Emma\ORM\EntityManager\Interfaces\EntityHydratorInterface;
use Emma\ORM\EntityManager\Interfaces\EntityManagerInterface;
use Emma\ORM\EntityManager\Interfaces\EntityResolverInterface;
use Emma\ORM\EntityManager\Interfaces\RelationshipManagerInterface;
use Emma\ORM\EntityManager\RelationshipManager\RelationshipManager;
use InvalidArgumentException;

class EntityManager implements EntityManagerInterface
{
    /**
     * @var PDOConnection|null
     */
    protected ?PDOConnection $connection;

    /**
     * @param EntityResolverInterface $entityResolver
     * @param EntityHydratorInterface $entityHydrator
     * @param RelationshipManagerInterface $relationshipManager
     */
    public function __construct(
        protected EntityResolverInterface $entityResolver,
        protected EntityHydratorInterface $entityHydrator,
        protected RelationshipManagerInterface $relationshipManager
    ) {
    }

    /**
     * @param object $entity
     * @return mixed
     * @throws \ReflectionException
     */
    public function persist(object &$entity): mixed
    {
        if ($entity::class !== $this->entityResolver->getEntity()::class) {
            throw new \BadMethodCallException("Invalid Call to Persist Unknown Object!");
        }

        $this->entityResolver->resolveForPersistence($entity);
        $violations = $this->entityResolver->getViolations();
        if ($violations->valid()) {
            return $violations;
        }

        $primaryKeys = $this->entityResolver->getPrimaryKey();
        if ($this->existsBy($primaryKeys)) {
            $state = $this->getUpdateQuery()->setQueryType(QueryType::UPDATE_STATEMENT)->execute();
        }
        else {
            $state = $this->getInsertQuery()->setQueryType(QueryType::INSERT_STATEMENT)->execute();
            if (is_numeric($state) && count($primaryKeys) == 1) {
                $entity = $this->entityHydrator->setPrimaryKey($entity, $primaryKeys, $state);
            }
        }
        return $state;
    }

    /**
     * @param array $entities
     * @return bool
     * @throws \Exception
     */
    public function persistAll(array &$entities): bool
    {
        foreach ($entities as $entity) {
            $this->persist($entity);
        }
        return true;
    }

    /**
     * @param WhereCompositeCondition|WhereCondition|array $criteria
     * @return int
     * @throws \Exception
     */
    public function count(WhereCompositeCondition|WhereCondition|array $criteria = []): int
    {
        $mysqlSelect = new Select($this->entityResolver->getEntity());
        $mysqlSelect->setTableName($this->entityResolver->getEntityAttributeInstance()->getFullTableName())
            ->setCriteria($criteria)
            ->setCriteriaDataTypes($this->entityResolver->getDataTypes());
        return $mysqlSelect->count();
    }

    /**
     * @param WhereCompositeCondition|WhereCondition|array $criteria
     * @return int
     * @throws \Exception
     */
    public function countBy(WhereCompositeCondition|WhereCondition|array $criteria = []): int
    {
        return $this->count($criteria);
    }

    /**
     * @param WhereCompositeCondition|WhereCondition|array $criteria
     * @return bool
     * @throws \Exception
     */
    public function existsBy(WhereCompositeCondition|WhereCondition|array $criteria): bool
    {
        return $this->count($criteria) > 0;
    }

    /**
     * @param string $column
     * @param array $criteria
     * @param int|null $limit
     * @param int|null $offset
     * @return int
     * @throws \Exception
     */
    public function sum(string $column, array $criteria = [], ?int $limit = null, ?int $offset = null): int
    {
        $mysqlSelect = new Select($this->entityResolver->getEntity());
        $mysqlSelect->setTableName($this->entityResolver->getEntityAttributeInstance()->getFullTableName())
            ->setCriteria($criteria)
            ->setLimit($limit)
            ->setOffset($offset)
            ->setCriteriaDataTypes($this->entityResolver->getDataTypes());
        return $mysqlSelect->sum($column);
    }

    /**
     * @param WhereCompositeCondition|WhereCondition|array $criteria
     * @param array $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param int|null $endLimit
     * @param string|null $fetchMode
     * @return mixed
     * @throws \Exception
     *
     * NOTE: USE this method directly, If Entity has NO relationship property(s).
     * Then, set the FetchMode to FETCH_CLASS and call this method.
     */
    public function find(
        WhereCompositeCondition|WhereCondition|array $criteria,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null,
        ?int $endLimit = null,
        string $fetchMode = null
    ): mixed
    {
        $mysqlSelect = new Select($this->entityResolver->getEntity());
        $mysqlSelect->setTableName($this->entityResolver->getEntityAttributeInstance()->getFullTableName())
            ->setCriteria($criteria)
            ->setOrderBy($orderBy)
            ->setLimit($limit)
            ->setEndLimit($endLimit)
            ->setOffset($offset)
            ->setFetchMode($fetchMode)
            ->setCriteriaDataTypes($this->entityResolver->getDataTypes());
        return $mysqlSelect->execute();
    }

    /**
     * @param WhereCompositeCondition|WhereCondition|array $criteria
     * @param array $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param int|null $endLimit
     * @return mixed
     * @throws \Exception
     */
    public function findBy(
        WhereCompositeCondition|WhereCondition|array $criteria,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null,
        ?int $endLimit = null
    ): mixed
    {
        $result = $this->find($criteria, $orderBy, $limit, $offset, $endLimit, FetchMode::FETCH_ASSOC);
        if (empty($result)) {
            return null;
        }
        return $this->relationshipManager->findEntityRelationship($result, $limit);
    }

    /**
     * @param WhereCompositeCondition|WhereCondition|array $criteria
     * @param array $orderBy
     * @return mixed
     * @throws \Exception
     */
    public function findOneBy(WhereCompositeCondition|WhereCondition|array $criteria, array $orderBy = []): mixed
    {
        return $this->findBy($criteria, $orderBy, 1);
    }

    /**
     * @param WhereCompositeCondition|WhereCondition|array $criteria
     * @param array $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param int|null $endLimit
     * @return mixed
     * @throws \Exception
     */
    public function findArrayBy(
        WhereCompositeCondition|WhereCondition|array $criteria,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null,
        ?int $endLimit = null
    ): mixed
    {
        $this->entityResolver->getEntityAttributeInstance()->setFetchAsArray(true);
        $result = $this->find($criteria, $orderBy, $limit, $offset, $endLimit, FetchMode::FETCH_ASSOC);
        if (empty($result)) {
            return null;
        }
        $result = $this->relationshipManager->findEntityRelationship($result, $limit);
        $this->entityResolver->getEntityAttributeInstance()->setFetchAsArray(false);
        return $result;
    }

    /**
     * @param WhereCompositeCondition|WhereCondition|array $criteria
     * @param array $orderBy
     * @return mixed
     * @throws \Exception
     */
    public function findOneArrayBy(WhereCompositeCondition|WhereCondition|array $criteria, array $orderBy = []): mixed
    {
        return $this->findArrayBy($criteria, $orderBy, 1);
    }

    /**
     * @param array $fieldValues
     * @param WhereCompositeCondition|WhereCondition|array $criteria
     * @return mixed
     * @throws \Exception
     */
    public function updateFields(array $fieldValues, WhereCompositeCondition|WhereCondition|array $criteria): mixed
    {
        $updatableFields = array_keys($this->entityResolver->getUpdatableKeyValue());
        $attemptingFields = array_keys($fieldValues);
        if (array_intersect($attemptingFields, $updatableFields) !== $attemptingFields) {
            throw new InvalidArgumentException("Invalid Fields: One or More Un-updatable field detected!");
        }

        $sqlQuery = new Query();
        $sqlQuery->QB()->update($fieldValues)->from($this->entityResolver->getEntityAttributeInstance()->getFullTableName());
        $sqlQuery = CriteriaHandler::handle($sqlQuery, $criteria, $this->entityResolver->getDataTypes());
        return $sqlQuery->execute();
    }

    /**
     * @param WhereCompositeCondition|WhereCondition|array $criteria
     * @return mixed
     * @throws \Exception
     */
    public function deleteBy(WhereCompositeCondition|WhereCondition|array $criteria): mixed
    {
        $sqlQuery = new Query();
        $sqlQuery->QB()->delete()->from($this->entityResolver->getEntityAttributeInstance()->getFullTableName());
        $sqlQuery = CriteriaHandler::handle($sqlQuery, $criteria, $this->entityResolver->getDataTypes());
        return $sqlQuery->execute();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function deleteAll(): mixed
    {
        return $this->deleteBy([]);
    }

    /**
     * @return EntityResolverInterface
     */
    public function getEntityResolver(): EntityResolverInterface
    {
        return $this->entityResolver;
    }

    /**
     * @return EntityHydrator|null
     */
    public function getEntityHydrator(): ?EntityHydratorInterface
    {
        return $this->entityHydrator;
    }

    /**
     * @return RelationshipManager|null
     */
    public function getRelationshipManager(): ?RelationshipManager
    {
        return $this->relationshipManager;
    }

    /**
     * @return Query
     * @throws \Exception
     */
    private function getInsertQuery(): Query
    {
        $query = new Query();
        $query->setQueryType(QueryType::INSERT_STATEMENT);
        $QB = $query->QB();
        $QB->into($this->entityResolver->getEntityAttributeInstance()->getFullTableName());

        $fieldSet = [];
        $KeyValuePair = $this->entityResolver->getInsertableKeyValuePair();
        $PKeyField = array_keys($this->entityResolver->getPrimaryKey());
        foreach ($KeyValuePair as $fieldName => $value) {
            if (!in_array($fieldName, $PKeyField)
                /** Is Partly PRIMARY KEY but has value for insert operation. that is, the primary key is not an Auto-Increment field. */
                || !empty($value)
            ) {
                $fieldSet[$fieldName] = $value;
                $QB->insertColumn($fieldName);
            }
        }

        $query->bindParams($fieldSet, $this->entityResolver->getDataTypes());
        return $query;
    }

    /**
     * @return Query
     * @throws \Exception
     */
    private function getUpdateQuery(): Query
    {
        $query = new Query();
        $QB = $query->QB();
        $QB->setTableName($this->entityResolver->getEntityAttributeInstance()->getFullTableName());

        $fieldSet = [];
        $KeyValuePair = $this->entityResolver->getUpdatableKeyValue();
        $primaryKey = $this->entityResolver->getPrimaryKey();
        $count = 0;
        foreach ($KeyValuePair as $fieldName => $value) {
            if (!isset($primaryKey[$fieldName])) {
                $fieldSet[$fieldName] = $value;
                $QB->updateColumn($fieldName);
            } else if ($count == 0) {
                $QB->where(WhereCondition::eq($fieldName, $value));
                $count++;
            } else {
                $QB->andWhere(WhereCondition::eq($fieldName, $value));
            }
        }

        $query->bindParams(array_merge($fieldSet, $primaryKey), $this->entityResolver->getDataTypes());
        return $query;
    }

    /**
     * @return PDOConnection
     */
    public function getConnection(): PDOConnection
    {
        if (is_null($this->connection)) {
            $this->connection = Connection::getInstance()->getActiveConnection();
        }
        return $this->connection;
    }

    /**
     * @param PDOConnection $connection
     * @return EntityManager
     */
    public function setConnection(PDOConnection $connection): static
    {
        $this->connection = $connection;
        return $this;
    }

}