<?php

namespace Emma\ORM\EntityManager\RelationshipManager;

use Emma\Dbal\QueryBuilder\Constants\FetchMode;
use Emma\Dbal\QueryBuilder\Constants\QueryType;
use Emma\Dbal\QueryBuilder\Expressions\QueryExpression;
use Emma\Dbal\QueryBuilder\Expressions\WhereCompositeCondition;
use Emma\Dbal\QueryBuilder\Query;
use Emma\ORM\Attributes\Relationships\JoinColumn;
use Emma\ORM\Attributes\Relationships\JoinColumnValue;
use Emma\ORM\Attributes\Relationships\JoinTable;
use Emma\ORM\Attributes\Relationships\RelationshipType;


class RelationshipQuery
{
    private string $parentTableAlias = "p";

    private string $targetEntityTableAlias = "t";

    protected string $relationshipTableAlias = "r";

    protected array $criteria;

    protected array $relationshipTableJoinConditions;

    protected array $parentTableJoinConditions;

    /**
     * @param array $parentData
     * @param string $parentTableName
     * @param RelationshipType $relationship
     * @param JoinColumn|null $joinColumn
     * @param JoinTable|null $joinTable
     * @param bool $isSingleResult
     * @throws \Exception
     */
    public function __construct(
        protected array $parentData,
        protected string $parentTableName,
        protected RelationshipType $relationship,
        protected ?JoinColumn $joinColumn,
        protected ?JoinTable $joinTable,
        protected bool $isSingleResult
    ) {
        if (!is_null($this->joinTable)) {
            $this->criteria = $this->prepareJoinTableCriteria(
                $this->joinTable->joinColumns,
                $this->relationshipTableAlias
            );
            $this->relationshipTableJoinConditions =  $this->prepareJoinConditions(
                $this->joinTable->inverseJoinColumns,
                $this->relationshipTableAlias,
                $this->targetEntityTableAlias
            );
            $this->parentTableJoinConditions = $this->prepareJoinConditions(
                $this->joinTable->joinColumns,
                $this->relationshipTableAlias,
                $this->parentTableAlias
            );
        } else {
            $this->criteria = $this->prepareJoinColumnCriteria(
                [$this->joinColumn], $this->parentTableAlias
            );
        }
        if (empty($this->criteria)) {
            throw new \Exception("Dependency Relationship Criteria Not Found!");
        }
    }

    /**
     * @return Query
     * @throws \Exception
     */
    public function getQuery(): Query
    {
        if (!is_null($this->joinTable)) {
            return $this->getJoinTableQuery();
        }
        return $this->getJoinColumnQuery();
    }

    /**
     * @return array|mixed|null
     * @throws \Exception
     */
    public function fetch(): mixed
    {
        return $this->getQuery()->setQueryType(
            $this->isSingleResult ? QueryType::SELECT_ONE_STATEMENT : QueryType::SELECT_STATEMENT
        )->execute();
    }

    /**
     * @return Query
     * @throws \Exception
     */
    protected function getJoinColumnQuery(): Query
    {
        $tableName = $this->relationship->getTargetEntityAttribute()->getFullTableName();

        $sqlQuery = new Query();
        $sqlQuery->setFetchMode(FetchMode::FETCH_ASSOC);
        $sqlQuery->QB()
            ->select([$this->targetEntityTableAlias.".*"])->from($tableName, $this->targetEntityTableAlias)
            ->where(WhereCompositeCondition::andX($this->criteria))
            ->setLimit($this->isSingleResult ? 1 : 0);
        foreach ($this->criteria as $val) {
            $sqlQuery->set($val);
        }
        return $sqlQuery;
    }

    /**
     * @return Query
     * @throws \Exception
     */
    protected function getJoinTableQuery(): Query
    {
        $tableName = $this->relationship->getTargetEntityAttribute()->getFullTableName();
        $relationshipTableName = $this->joinTable->name;

        $sqlQuery = new Query();
        $sqlQuery->setFetchMode(FetchMode::FETCH_ASSOC);
        $sqlQuery->QB()
            ->select([$this->targetEntityTableAlias.".*"])->from($tableName, $this->targetEntityTableAlias)
            ->leftJoin("$relationshipTableName " . $this->relationshipTableAlias, implode(" AND ", $this->relationshipTableJoinConditions))
            ->leftJoin("$this->parentTableName ".$this->parentTableAlias, implode(" AND ", $this->parentTableJoinConditions))
            ->where(WhereCompositeCondition::andX($this->criteria))
            ->setLimit($this->isSingleResult ? 1 : 0);
        foreach ($this->criteria as $val) {
            $sqlQuery->set($val);
        }
        return $sqlQuery;
    }

    /**
     * @param array|JoinColumn[] $joinColumns
     * @param string $columnAlias
     * @return array
     */
    private function prepareJoinColumnCriteria(array $joinColumns, string $columnAlias = ""): array
    {
        $criteria = [];
        $columnAlias = !empty($columnAlias) ? $columnAlias."." : $columnAlias;
        foreach($joinColumns as $joinColumn) {
            $value = $joinColumn instanceof JoinColumnValue ? $joinColumn->value : $this->parentData[$joinColumn->name];
            $criteria[$columnAlias.$joinColumn->referencedColumnName] = $value;
        }
        return $criteria;
    }

    /**
     * @param array|JoinColumn[] $joinColumns
     * @param string $columnAlias
     * @return array
     */
    private function prepareJoinTableCriteria(array $joinColumns, string $columnAlias = ""): array
    {
        $criteria = [];
        $columnAlias = !empty($columnAlias) ? $columnAlias."." : $columnAlias;
        foreach($joinColumns as $joinColumn) {
            $referencedColumnNameValue = $joinColumn instanceof JoinColumnValue ? $joinColumn->value : $this->parentData[$joinColumn->referencedColumnName];
            $criteria[$columnAlias.$joinColumn->name] = $referencedColumnNameValue;
        }
        return $criteria;
    }

    /**
     * @param array|JoinColumn[] $joinColumns
     * @param string $fromAlias
     * @param string $toAlias
     * @return array
     */
    private function prepareJoinConditions(array $joinColumns, string $fromAlias, string $toAlias): array
    {
        $joinConditions = [];
        $fromAlias = !empty($fromAlias) ? $fromAlias."." : $fromAlias;
        $toAlias = !empty($toAlias) ? $toAlias."." : $toAlias;
        foreach($joinColumns as $joinColumn) {
            $referencedColumnNameValue = $joinColumn instanceof JoinColumnValue ? $joinColumn->value : $toAlias.$joinColumn->referencedColumnName;
            if (is_array($referencedColumnNameValue)) {
                $joinConditions[] = QueryExpression::in($fromAlias.$joinColumn->name, $referencedColumnNameValue);
            } else {
                $joinConditions[] = QueryExpression::eq($fromAlias.$joinColumn->name, $referencedColumnNameValue);
            }
        }
        return $joinConditions;
    }
}