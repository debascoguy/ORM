<?php

namespace Emma\ORM\Attributes\Relationships;

use Attribute;
use Emma\ORM\Attributes\Entity\Property\Column;

#[Attribute(Attribute::TARGET_PROPERTY)]
class JoinColumn extends Column
{
    public function __construct(
        public string $name,
        public string $referencedColumnName = "",
        public bool $unique = false,
        public bool $nullable = true,
        public bool $insertable = true,
        public bool $updatable = true,
        public String $columnDefinition = "",
        public string $tableName = "",
        public ForeignKey $foreignKey = new ForeignKey("")
    ) {
        parent::__construct($name, $unique, $nullable, $insertable, $updatable, $columnDefinition, $tableName);
    }
}