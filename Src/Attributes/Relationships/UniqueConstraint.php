<?php

namespace Emma\ORM\Attributes\Relationships;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class UniqueConstraint extends Relationship
{
    public function __construct(public string $name = "", public array $columnName = [])
    {

    }
}