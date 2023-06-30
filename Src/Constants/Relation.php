<?php

namespace Emma\ORM\Constants;

class Relation {

    public const ONE_TO_ONE = "OneToOne";

    public const ONE_TO_MANY = "OneToMany";

    public const MANY_TO_ONE = "ManyToOne";
    
    public const MANY_TO_MANY = "ManyToMany";

    public static function isValidRelationship($relation): bool
    {
        return match ($relation) {
            Relation::ONE_TO_ONE, Relation::ONE_TO_MANY, Relation::MANY_TO_ONE, Relation::MANY_TO_MANY => true,
            default => false,
        };
    }

    /**
     * @param string $relation
     * @return mixed
     */
    public static function findSelectLimit(string $relation)
    {
        return Relation::ONE_TO_ONE == $relation || Relation::MANY_TO_ONE == $relation ? 1 : null;
    }
}