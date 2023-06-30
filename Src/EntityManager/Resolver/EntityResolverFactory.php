<?php

namespace Emma\ORM\EntityManager\Resolver;

use ArrayIterator;
use Emma\Common\Factory\ObjectFactory;
use Emma\ORM\Attributes\Entity\Entity;
use Emma\ORM\EntityManager\Interfaces\EntityResolverInterface;
use ReflectionObject;

class EntityResolverFactory extends ObjectFactory implements EntityResolverInterface
{
    protected EntityResolverInterface $entityResolver;

    public function make(object|string $entity): static
    {
        $this->entityResolver = new EntityResolver($entity);
        return $this;
    }

    public function initialize(): static
    {
        $this->entityResolver->initialize();
        return $this;
    }

    public function resolveForPersistence(object $entity = null): void
    {
        $this->entityResolver->resolveForPersistence($entity);
    }

    public function getReflectionObject(): ReflectionObject
    {
        return $this->entityResolver->getReflectionObject();
    }

    public function getPropertyNames(): array
    {
        return $this->entityResolver->getPropertyNames();
    }

    public function hasRelationship(): bool
    {
        return $this->entityResolver->hasRelationship();
    }

    public function getEntityRelationships(): array
    {
        return $this->entityResolver->getEntityRelationships();
    }

    public function getEntityAttributeInstance(): ?Entity
    {
        return $this->entityResolver->getEntityAttributeInstance();
    }

    public function getAllResolvedFields(): array
    {
        return $this->entityResolver->getAllResolvedFields();
    }

    public function getUpdatableKeyValue(): array
    {
        return $this->entityResolver->getUpdatableKeyValue();
    }

    public function getInsertableKeyValuePair(): array
    {
        return $this->entityResolver->getInsertableKeyValuePair();
    }

    public function getDataTypes(): array
    {
        return $this->entityResolver->getDataTypes();
    }

    public function getPrimaryKey(): array
    {
        return $this->entityResolver->getPrimaryKey();
    }

    public function getForeignKey(): array
    {
        return $this->entityResolver->getForeignKey();
    }

    public function getUniqueKey(): array
    {
        return $this->entityResolver->getUniqueKey();
    }

    public function getEntity(): object
    {
        return $this->entityResolver->getEntity();
    }

    public function getViolations(): ArrayIterator
    {
        return $this->entityResolver->getViolations();
    }

}