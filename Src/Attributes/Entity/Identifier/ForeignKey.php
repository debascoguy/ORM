<?php

namespace Emma\ORM\Attributes\Entity\Identifier;

use Attribute;
use Emma\ORM\Attributes\Entity\Property\Property;
use Emma\ORM\Constants\ConstraintType;
use Emma\ORM\Constants\GenerationType;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ForeignKey extends Id
{
    /** Default to the PROPERTY Name if set to null */
    public function __construct(public ?string $fieldName = null, public string $strategy = GenerationType::IDENTITY)
    {
        parent::__construct($this->fieldName, ConstraintType::FOREIGN_KEY, $this->strategy);
    }
}