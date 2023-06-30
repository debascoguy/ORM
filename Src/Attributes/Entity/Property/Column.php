<?php

namespace Emma\ORM\Attributes\Entity\Property;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column extends Property
{
    public function __construct(
        public string $name,
        public bool $unique = false,
        public bool $nullable = true,
        public bool $insertable = true,
        public bool $updatable = true,
        public String $columnDefinition = "",
        public string $tableName = "",
        public int $length = 255,
        /**  (Optional) The precision for a decimal (exact numeric) column. (Applies only if a decimal column is used.) */
        public int $precision = 0,
        /** (Optional) The scale for a decimal (exact numeric) column. (Applies only if a decimal column is used.) */
        public int $scale = 0 // the scale is the number of digits to the right of the decimal point
    ) {

    }
}