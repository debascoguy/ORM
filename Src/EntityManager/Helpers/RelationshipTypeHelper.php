<?php

namespace Emma\ORM\EntityManager\Helpers;

use Emma\ORM\Attributes\Relationships\JoinColumn;
use Emma\ORM\Attributes\Relationships\JoinTable;
use Emma\ORM\Attributes\Relationships\RelationshipType;
use Emma\ORM\Constants\Relation;

class RelationshipTypeHelper
{
    /**
     * @param array $entityRelationship
     * @return RelationshipType|null
     */
    public static function findRelationshipType(array $entityRelationship): ?RelationshipType
    {
        return $entityRelationship[Relation::ONE_TO_ONE] ??
            $entityRelationship[Relation::ONE_TO_MANY] ??
            $entityRelationship[Relation::MANY_TO_ONE] ??
            $entityRelationship[Relation::MANY_TO_MANY] ?? null;
    }

    /**
     * @param array $entityRelationship
     * @return JoinColumn|null
     */
    public static function findJoinColumn(array $entityRelationship): ?JoinColumn
    {
        return $entityRelationship["JoinColumn"] ?? null;
    }

    /**
     * @param array $entityRelationship
     * @return JoinTable|null
     */
    public static function findJoinTable(array $entityRelationship): ?JoinTable
    {
        return $entityRelationship["JoinTable"] ?? null;
    }

    /**
     * @param int|null $limit
     * @param string $relation
     * @return bool
     */
    public static function isSingleResult(?int $limit, string $relation): bool
    {
        if (Relation::ONE_TO_MANY == $relation || Relation::MANY_TO_MANY == $relation) {
            return false;
        } else {
            return (is_int($limit) && $limit == 1) || Relation::ONE_TO_ONE == $relation || Relation::MANY_TO_ONE == $relation;
        }
    }
}