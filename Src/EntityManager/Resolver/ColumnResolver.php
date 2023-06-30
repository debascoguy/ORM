<?php

namespace Emma\ORM\EntityManager\Resolver;

use Emma\ORM\Attributes\Entity\Property\Column;
use Emma\ORM\Attributes\Relationships\JoinColumn;
use Emma\ORM\EntityManager\Helpers\RelationshipTypeHelper;
use InvalidArgumentException;
use ReflectionProperty;

trait ColumnResolver
{
    use RelationshipResolver;

    protected ?string $columnPropertyName  = null;

    protected ?Column $column = null;

    /**
     * @param ReflectionProperty $property
     * @return Column|JoinColumn|null
     */
    public function getColumn(ReflectionProperty $property): Column|JoinColumn|null
    {
        $propertyName = $property->getName();
        if ($propertyName == $this->columnPropertyName) {
            return $this->column;
        }

        $this->columnPropertyName = $propertyName;

        /** @var \ReflectionAttribute[]|array $columnAttributes */
        $columnAttributes = $property->getAttributes(Column::class, \ReflectionAttribute::IS_INSTANCEOF);
        $this->column = null;
        if (count($columnAttributes) > 1) {
            throw new InvalidArgumentException("Invalid Column Definition. ONLY one instance of Column or JoinColumn is needed!");
        }

        if (empty($columnAttributes)) {
            $relationshipsInstances = $this->resolveRelationships($property);
            if (empty($relationshipsInstances)) {
                $this->column = new Column($propertyName);
                return $this->column;
            }

            $joinColumn = RelationshipTypeHelper::findJoinColumn($relationshipsInstances);
            if (!empty($joinColumn)) {
                $this->column = $joinColumn;
                return $this->column;
            }
            $this->column = null;
            return $this->column;
        }

        /** @var Column|JoinColumn $column */
        $this->column = $columnAttributes[0]->newInstance();
        return $this->column;
    }

    /**
     * @param ReflectionProperty $property
     * @return string
     */
    public function getColumnName(ReflectionProperty $property): string
    {
        return $this->getColumn($property)?->name ?? $property->getName();
    }

}