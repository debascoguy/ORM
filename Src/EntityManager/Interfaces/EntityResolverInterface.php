<?php

namespace Emma\ORM\EntityManager\Interfaces;

use ArrayIterator;
use Emma\ORM\Attributes\Entity\Entity;
use ReflectionObject;

interface EntityResolverInterface
{
    public function initialize(): static;

    public function resolveForPersistence(object $entity = null): void;

    public function getReflectionObject(): ReflectionObject;

    public function getPropertyNames(): array;

    public function hasRelationship(): bool;

    public function getEntityRelationships(): array;

    public function getEntityAttributeInstance(): ?Entity;

    public function getAllResolvedFields(): array;

    public function getUpdatableKeyValue(): array;

    public function getInsertableKeyValuePair(): array;

    public function getDataTypes(): array;

    public function getPrimaryKey(): array;

    public function getForeignKey(): array;

    public function getUniqueKey(): array;

    public function getEntity(): object;

    public function getViolations(): ArrayIterator;
}