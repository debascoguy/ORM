<?php

namespace Emma\ORM\EntityManager\Resolver;

use Emma\ORM\EntityManager\Utils\PropertyInfo;
use Emma\Validation\Resolver\DataTypeConverterResolver;
use Emma\Validation\Resolver\ValidationResolver;
use InvalidArgumentException;
use ReflectionProperty;

class PropertyResolver
{
    use RelationshipResolver;
    use IdentifierResolver;
    use ColumnResolver;
    use DataTypeConverterResolver;
    use ValidationResolver;

    /**
     * @param ReflectionProperty $property
     * @return PropertyInfo
     */
    public function initialize(ReflectionProperty $property): PropertyInfo
    {
        $dataType = $property->getType();
        if (empty($dataType)) {
            throw new InvalidArgumentException("Invalid Data Type For: " .  $property->getName());
        }


        $propertyInfo = new PropertyInfo();
        $propertyInfo->setPropertyName($property->getName());
        $propertyInfo->setDataType($dataType->getName());

        $relationship = $this->resolveRelationships($property);

        $propertyInfo->setRelationships($relationship);
        $propertyInfo->setColumn($this->getColumn($property))->setColumnName($this->getColumnName($property));
        $propertyInfo->setId($this->resolveIdentifiers($property));
        $propertyInfo->setReflectionProperty($property);
        return $propertyInfo;
    }

    /**
     * @param PropertyInfo $propertyInfo
     * @param $entity
     * @return PropertyInfo
     */
    public function resolveEntityValue(PropertyInfo $propertyInfo, $entity): PropertyInfo
    {
        if ($propertyInfo->hasManyRelationships()) {
            //TODO: resolve for children/parent CRUD persistence operations -  in future release
            return $propertyInfo;
        }

        $property = $propertyInfo->getReflectionProperty();
        $columnValue = $property->getValue($entity);
        $columnValue = $this->resolveInput($property, $columnValue);
        if ($columnValue == null && $property->hasDefaultValue()) {
            $columnValue =  $property->getDefaultValue();
        }
        if ($columnValue == null && !$propertyInfo->getColumn()?->nullable) {
            $className = get_class($entity);
            $columnName = $propertyInfo->getColumnName();
            throw new InvalidArgumentException("Null value Set For $className :: $columnName And it's not nullable!");
        }
        $columnValue = $this->validateProperty($property, $columnValue, $entity);
        $propertyInfo->setColumnValue($columnValue);
        if ($columnValue != null) {
            $propertyInfo->setDataType(gettype($columnValue));
        }
        return $propertyInfo;
    }

}