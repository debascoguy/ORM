<?php

namespace Emma\ORM\EntityManager\Interfaces;

use Emma\Dbal\Connection\PDOConnection;
use Emma\Dbal\QueryBuilder\Expressions\WhereCompositeCondition;
use Emma\Dbal\QueryBuilder\Expressions\WhereCondition;

interface EntityManagerInterface
{
    public function persist(object &$entity): mixed;

    public function persistAll(array &$entities): bool;

    public function existsBy(WhereCompositeCondition|WhereCondition|array $criteria): bool;

    public function count(WhereCompositeCondition|WhereCondition|array $criteria = []): int;

    public function countBy(WhereCompositeCondition|WhereCondition|array $criteria = []): int;

    public function sum(string $column, array $criteria = [], ?int $limit = null, ?int $offset = null): int;

    public function find(
        WhereCompositeCondition|WhereCondition|array $criteria,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null,
        ?int $endLimit = null,
        string $fetchMode = null
    ): mixed;

    public function findOneBy(WhereCompositeCondition|WhereCondition|array $criteria, array $orderBy = []): mixed;

    public function findArrayBy(
        WhereCompositeCondition|WhereCondition|array $criteria,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null,
        ?int $endLimit = null
    ): mixed;

    public function findOneArrayBy(WhereCompositeCondition|WhereCondition|array $criteria, array $orderBy = []): mixed;

    public function findBy(
        WhereCompositeCondition|WhereCondition|array $criteria,
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null,
        ?int $endLimit = null,
    ): mixed;

    public function updateFields(array $fieldValues, WhereCompositeCondition|WhereCondition|array $criteria): mixed;

    public function deleteBy(WhereCompositeCondition|WhereCondition|array $criteria): mixed;

    public function deleteAll();

    public function getEntityResolver(): EntityResolverInterface;

    public function getConnection(): PDOConnection;

    public function setConnection(PDOConnection $connection): static;
}