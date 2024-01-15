<?php

namespace Emma\ORM\EntityManager\Resolver;

use Emma\ORM\Attributes\Relationships\JoinColumn;
use Emma\ORM\Attributes\Relationships\JoinTable;
use Emma\ORM\Attributes\Relationships\Relationship;
use Emma\ORM\Attributes\Relationships\RelationshipType;
use Emma\ORM\EntityManager\Utils\ClassBaseName;
use ReflectionProperty;

trait RelationshipResolver
{
    use ClassBaseName;

    /**
     * @var string|null
     */
    protected ?string $relationshipPropertyName  = null;

    /**
     * @var array
     */
    protected array $relationships;

    /**
     * @param ReflectionProperty $property
     * @return array
     */
    protected function resolveRelationships(ReflectionProperty $property): array
    {
        $propertyName = $property->getName();
        if ($propertyName == $this->relationshipPropertyName) {
            return $this->relationships;
        }

        $this->relationshipPropertyName = $propertyName;

        /** @var \ReflectionAttribute[]|array $relationships */
        $relationships = $property->getAttributes(Relationship::class, \ReflectionAttribute::IS_INSTANCEOF);
        $relationshipsInstances = [];
        foreach ($relationships as $relationship) {
            /** @var RelationshipType|JoinTable $relInstance */
            $relInstance = $relationship->newInstance();
            $relationshipsInstances[$this->baseName($relInstance::class)] = $relInstance;
        }

        /** @var \ReflectionAttribute[]|array $relationships */
        $relationships = $property->getAttributes(JoinColumn::class, \ReflectionAttribute::IS_INSTANCEOF);
        if (!empty($relationships)) {
            /** @var JoinColumn $relInstance */
            $relInstance = $relationships[0]->newInstance();
            $relationshipsInstances[$this->baseName($relInstance::class)] = $relInstance;
        }
        $this->relationships = $relationshipsInstances;
        return $this->relationships;
    }
    
}