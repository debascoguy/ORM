<?php

namespace Emma\ORM\Attributes\Relationships;

use Attribute;
use Emma\ORM\Attributes\Entity\Property\Column;
use Emma\ORM\Constants\ConstraintMode;

#[Attribute(Attribute::TARGET_PROPERTY)]
class JoinColumnValue extends JoinColumn
{
    public function __construct(
        public string     $name,
        /** Joining another Table on VALUE rather than a referenceColumnName */
        public mixed      $value,
        public bool       $unique = false,
        public bool       $nullable = true,
        public bool       $insertable = true,
        public bool       $updatable = true,
        public string     $columnDefinition = "",
        public string     $tableName = "",
        public ForeignKey $foreignKey = new ForeignKey("")
    )
    {
        $this->value = "'".addslashes($this->value)."'";
        parent::__construct(
            $name,
            "",
            $unique,
            $nullable,
            $insertable,
            $updatable,
            $columnDefinition,
            $tableName,
            $foreignKey
        );
    }
}