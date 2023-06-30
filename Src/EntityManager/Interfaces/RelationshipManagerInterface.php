<?php

namespace Emma\ORM\EntityManager\Interfaces;

interface RelationshipManagerInterface
{
    public function findEntityRelationship(array $result, $limit): mixed;

    public function prepare($result, $isSingleResult): mixed;
}