<?php

namespace Emma\ORM\EntityManager\Utils;

use Emma\Common\Property\Property;
use Emma\ORM\Attributes\Entity\Identifier\Id;
use Emma\ORM\Attributes\Entity\Property\Column;
use Emma\ORM\Attributes\Relationships\JoinColumn;
use ReflectionProperty;

class PropertyInfo extends Property
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param ReflectionProperty $property
     * @param object $entity
     * @param bool $isPersist
     * @return static
     * @throws \ReflectionException
     */

    /**
     * @return string
     */
    public function getPropertyName(): string
    {
        return $this->get("PROPERTY_NAME");
    }

    /**
     * @param string $propertyName
     * @return PropertyInfo
     */
    public function setPropertyName(string $propertyName): static
    {
        $this->register("PROPERTY_NAME", $propertyName);
        return $this;
    }

    /**
     * @return string
     */
    public function getDataType(): string
    {
        return $this->get("DATA_TYPE");
    }

    /**
     * @param string $dataType
     * @return PropertyInfo
     */
    public function setDataType(string $dataType): static
    {
        $this->register("DATA_TYPE", $dataType);
        return $this;
    }

    /**
     * @return string
     */
    public function getColumnName(): string
    {
        return $this->get("COLUMN_NAME");
    }

    /**
     * @param string $columnName
     * @return PropertyInfo
     */
    public function setColumnName(string $columnName): static
    {
        $this->register("COLUMN_NAME", $columnName);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getColumnValue(): mixed
    {
        return $this->get("COLUMN_VALUE");
    }

    /**
     * @param mixed $columnValue
     * @return $this
     */
    public function setColumnValue(mixed $columnValue): static
    {
        $this->register("COLUMN_VALUE", $columnValue);
        return $this;
    }

    /**
     * @return Column|JoinColumn|null
     */
    public function getColumn(): Column|JoinColumn|null
    {
        return $this->get("COLUMN");
    }

    /**
     * @param Column|JoinColumn|null $column
     * @return PropertyInfo
     */
    public function setColumn(Column|JoinColumn|null $column): static
    {
        $this->register("COLUMN", $column);
        return $this;
    }

    /**
     * @return Id|null
     */
    public function getId(): ?Id
    {
        return $this->get("ID");
    }

    /**
     * @param Id|null $id
     * @return PropertyInfo
     */
    public function setId(?Id $id): static
    {
        $this->register("ID", $id);
        return $this;
    }

    /**
     * @return array
     */
    public function getRelationships(): array
    {
        return $this->get("RELATIONSHIPS", []);
    }

    /**
     * @param array $relationships
     * @return PropertyInfo
     */
    public function setRelationships(array $relationships): static
    {
        $this->register("RELATIONSHIPS", $relationships);
        return $this;
    }

    public function hasManyRelationships(): bool
    {
        $relationship = $this->getRelationships();
        if (isset($relationship["JoinTable"]) 
            || isset($relationship["OneToMany"]) 
            || isset($relationship["ManyToOne"]) 
            || isset($relationship["ManyToMany"])) {
            //TODO: resolve for children/parent CRUD persistence operations -  in future release
            return true;
        }
        return false;
    }

    public function setReflectionProperty(ReflectionProperty $property): static
    {
        $this->register("REFLECTION_PROPERTY", $property);
        return $this;
    }

    /**
     * @return ReflectionProperty|null
     */
    public function getReflectionProperty(): ?ReflectionProperty
    {
        return $this->get("REFLECTION_PROPERTY");
    }

}