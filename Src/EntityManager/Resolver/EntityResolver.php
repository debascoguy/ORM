<?php

namespace Emma\ORM\EntityManager\Resolver;

use ArrayIterator;
use Emma\Di\Container\ContainerManager;
use Emma\ORM\Attributes\Entity\Entity;
use Emma\ORM\Attributes\Entity\Property\Transient;
use Emma\ORM\Constants\ConstraintType;
use Emma\ORM\EntityManager\Interfaces\EntityResolverInterface;
use Emma\ORM\EntityManager\Utils\PropertyInfo;
use ReflectionObject;

class EntityResolver implements EntityResolverInterface
{
    use ContainerManager;

    protected ReflectionObject $reflectionObject;

    /**
     * @var Entity|null
     */
    protected ?Entity $entityAttributeInstance = null;

    /**
     * @var array
     */
    protected array $properties = [];

    /**
     * @var array
     */
    protected array $propertyNames = [];

    /**
     * @var array
     */
    protected array $allResolvedFields = [];

    /**
     * @var array
     */
    protected array $updatableKeyValue = [];

    /**
     * @var array
     */
    protected array $insertableKeyValuePair = [];

    /**
     * @var array
     */
    protected array $dataTypes = [];

    /**
     * @var array
     */
    protected array $identifiers = [];

    /**
     * @var array
     */
    protected array $entityRelationships = [];

    /**
     * @var array
     */
    protected array $violations = [];

    /**
     * @var PropertyResolver|null
     */
    protected  ?PropertyResolver $propertyResolver = null;

    /**
     * @param object|string $entity
     */
    public function __construct(protected object|string $entity)
    {
        $this->entity = is_object($this->entity) ? $this->entity : $this->getContainer()->create($this->entity);
        $this->reflectionObject = new \ReflectionObject($this->entity);
        $entityAttribute = $this->reflectionObject->getAttributes(Entity::class);
        if (empty($entityAttribute)) {
            throw new \InvalidArgumentException("Invalid Entity Detected For Entity Resolver!");
        }
        /** @var Entity $entityAttributeInstance */
        $this->entityAttributeInstance = $entityAttribute[0]->newInstance();
        $this->properties = $this->reflectionObject->getProperties();
        $this->propertyResolver = $this->getContainer()->get(PropertyResolver::class);
        $this->initialize();
    }

    /**
     * @return $this
     */
    public function initialize(): static
    {
        foreach($this->properties as $property) {
            /** @var \ReflectionAttribute[]|array $ColumnAttributes */
            $columnAttributes = $property->getAttributes(Transient::class, \ReflectionAttribute::IS_INSTANCEOF);
            if (!empty($columnAttributes)) {
                continue;
            }

            $field = $this->propertyResolver->initialize($property);
            $columnName = $field->getColumnName();
            $this->propertyNames[$field->getPropertyName()] = $field->getPropertyName();
            $this->dataTypes[$columnName] = $field->getDataType();
            if (!$field->hasManyRelationships()) {
                $column = $field->getColumn();
                if (!is_null($column) && $column->insertable) {
                    $this->insertableKeyValuePair[$columnName] = null;
                }
                if (!is_null($column) && $column->updatable) {
                    $this->updatableKeyValue[$columnName] = null;
                }
                if (!empty($id)) {
                    $this->identifiers[$id->constraintType][$columnName] = null;
                }
            }
            $relationships = $field->getRelationships();
            if (!empty($relationships)) {
                $this->entityRelationships[$property->getName()] = $relationships;
            }
            $this->allResolvedFields[$columnName] = $field;
        }
        return $this;
    }

    /**
     * @param object|null $entity
     * @return void
     * @throws \Exception
     */
    public function resolveForPersistence(object $entity = null): void
    {
        /**
         * @var string $columnName
         * @var PropertyInfo $field
         */
        foreach($this->allResolvedFields as $columnName => $field) {
            $field = $this->propertyResolver->resolveEntityValue($field, $entity ?? $this->entity);
            $errors = $this->propertyResolver->getErrors();
            if (!empty($errors)) {
                $this->violations[$field->getPropertyName()] = $this->propertyResolver->getErrors();
            }

            $id = $field->getId();
            $this->dataTypes[$columnName] = $field->getDataType();
            if (!$field->hasManyRelationships()) {
                $column = $field->getColumn();
                if (!is_null($column) && $column->insertable) {
                    $this->insertableKeyValuePair[$columnName] = $field->getColumnValue();
                }
                if (!is_null($column) && $column->updatable) {
                    $this->updatableKeyValue[$columnName] = $field->getColumnValue();
                }
                if (!empty($id)) {
                    $this->identifiers[$id->constraintType][$columnName] = $field->getColumnValue();
                }
            }
            $this->allResolvedFields[$columnName] = $field;
        }
    }

    /**
     * @return array
     */
    public function getPropertyNames(): array
    {
        return $this->propertyNames;
    }

    /**
     * @return bool
     */
    public function hasRelationship(): bool
    {
        return count($this->entityRelationships) > 0;
    }

    /**
     * @return array
     */
    public function getEntityRelationships(): array
    {
        return $this->entityRelationships;
    }

    /**
     * @return ReflectionObject
     */
    public function getReflectionObject(): ReflectionObject
    {
        return $this->reflectionObject;
    }

    /**
     * @param ReflectionObject $reflectionObject
     * @return EntityResolver
     */
    public function setReflectionObject(ReflectionObject $reflectionObject): static
    {
        $this->reflectionObject = $reflectionObject;
        return $this;
    }

    /**
     * @return Entity|null
     */
    public function getEntityAttributeInstance(): ?Entity
    {
        return $this->entityAttributeInstance;
    }

    /**
     * @return array
     */
    public function getAllResolvedFields(): array
    {
        return $this->allResolvedFields;
    }

    /**
     * @return array
     */
    public function getUpdatableKeyValue(): array
    {
        return $this->updatableKeyValue;
    }

    /**
     * @return array
     */
    public function getInsertableKeyValuePair(): array
    {
        return $this->insertableKeyValuePair;
    }

    /**
     * @return array
     */
    public function getDataTypes(): array
    {
        return $this->dataTypes;
    }

    /**
     * @return array
     */
    public function getPrimaryKey(): array
    {
        return $this->identifiers[ConstraintType::PRIMARY_KEY];
    }

    /**
     * @return array
     */
    public function getForeignKey(): array
    {
        return $this->identifiers[ConstraintType::FOREIGN_KEY];
    }

    /**
     * @return array
     */
    public function getUniqueKey(): array
    {
        return $this->identifiers[ConstraintType::UNIQUE];
    }

    /**
     * @return object
     */
    public function getEntity(): object
    {
        return $this->entity;
    }

    /**
     * @return ArrayIterator
     */
    public function getViolations(): ArrayIterator
    {
        return new ArrayIterator($this->violations);
    }

}