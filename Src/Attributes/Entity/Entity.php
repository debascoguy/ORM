<?php

namespace Emma\ORM\Attributes\Entity;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class Entity
{
    private bool $fetchAsArray = false;

    public function __construct(public string $tableName, public string $schema = "")
    {

    }

    /**
     * @return string
     */
    public function getFullTableName(): string
    {
        return !empty($this->schema) ? $this->schema.".".$this->tableName : $this->tableName;
    }

    /**
     * @return bool
     */
    public function fetchAsArray(): bool
    {
        return $this->fetchAsArray;
    }

    /**
     * @param bool $fetchAsArray
     * @return Entity
     */
    public function setFetchAsArray(bool $fetchAsArray): Entity
    {
        $this->fetchAsArray = $fetchAsArray;
        return $this;
    }
}