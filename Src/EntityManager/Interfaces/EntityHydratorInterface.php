<?php

namespace Emma\ORM\EntityManager\Interfaces;

interface EntityHydratorInterface
{
    public function hydrate(array $row): object;

    public function setPrimaryKey(object $entity, array $primaryKeys, int|string $value): object;
}