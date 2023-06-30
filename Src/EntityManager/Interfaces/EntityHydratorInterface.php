<?php

namespace Emma\ORM\EntityManager\Interfaces;

interface EntityHydratorInterface
{
    public function hydrate(array $row): object;

}