<?php

namespace Emma\ORM\Attributes\Entity\Property;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Transient
{
    /** Ignore target property from persistence and/or database operations. */
}