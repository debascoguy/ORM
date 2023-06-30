<?php

namespace Emma\ORM\Attributes\Relationships;

use Attribute;
use Emma\ORM\Constants\ConstraintMode;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ForeignKey extends Relationship
{
    public function __construct(
        public string $name = "",
        public string $value = ConstraintMode::PROVIDER_DEFAULT,
        public string $foreignKeyDefinition = "",
    ) {

    }
}