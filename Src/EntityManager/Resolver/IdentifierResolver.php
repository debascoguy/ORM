<?php

namespace Emma\ORM\EntityManager\Resolver;

use Emma\ORM\Attributes\Entity\Identifier\Id;
use InvalidArgumentException;
use ReflectionProperty;

trait IdentifierResolver
{
    use ColumnResolver;

    /**
     * @var string|null
     */
    protected ?string $idPropertyName  = null;

    /**
     * @var Id|null
     */
    protected ?Id $id = null;

    /**
     * @param ReflectionProperty $property
     * @return Id|null
     */
    protected function resolveIdentifiers(ReflectionProperty $property): ?Id
    {
        $propertyName = $property->getName();
        if ($propertyName == $this->idPropertyName) {
            return $this->id;
        }

        $this->idPropertyName = $propertyName;
        /** @var \ReflectionAttribute[]|array $IdAttributes */
        $IdAttributes = $property->getAttributes(Id::class, \ReflectionAttribute::IS_INSTANCEOF);
        $this->id = null;
        if (empty($IdAttributes)) {
            return $this->id;
        }
        if (count($IdAttributes) > 1) {
            throw new InvalidArgumentException("Invalid Column Identifier. ONLY one instance of an ID is allowed!");
        }
        $this->id = $IdAttributes[0]->newInstance();
        if (!empty($this->id) && empty($this->id->fieldName)) {
            $this->id->fieldName = $this->getColumnName($property);
        }
        return $this->id;
    }

}