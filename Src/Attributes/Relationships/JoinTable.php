<?php

namespace Emma\ORM\Attributes\Relationships;

use Attribute;
use Emma\ORM\Constants\ConstraintMode;

#[Attribute(Attribute::TARGET_PROPERTY)]
class JoinTable extends Relationship
{
    public function __construct(
        public string $name,
        /** @var JoinColumn[] $joinColumns */
        public array $joinColumns,
        /** @var JoinColumn[] $inverseJoinColumns */
        public array $inverseJoinColumns,
        public string $catalog = "",
        public string $schema = "",
        public ?ForeignKey $foreignKey = null,
        public ?ForeignKey $inverseForeignKey = null,
        public ?UniqueConstraint $uniqueConstraint = null
    ) {
        if (empty($joinColumns)) {
            throw new \InvalidArgumentException("Invalid JoinColumn. At least one JoinColumn param is required!");
        }

        if (empty($inverseJoinColumns)) {
            throw new \InvalidArgumentException("Invalid InverseJoinColumn. At least one JoinColumn param is required!");
        }

        foreach($joinColumns as $joinColumn) {
            if (!$joinColumn instanceof JoinColumn) {
                throw new \InvalidArgumentException("Invalid JoinColumn. JoinColumn param MUST be in instance of JoinColumn Attributes!");
            }
        }

        foreach($inverseJoinColumns as $joinColumn) {
            if (!$joinColumn instanceof JoinColumn) {
                throw new \InvalidArgumentException("Invalid JoinColumn. JoinColumn param MUST be in instance of JoinColumn Attributes!");
            }
        }

        if ($this->foreignKey == null) {
            $this->foreignKey = new ForeignKey($this->joinColumns[0]->name, ConstraintMode::PROVIDER_DEFAULT);
        }
        if ($this->inverseForeignKey == null) {
            $this->inverseForeignKey = new ForeignKey($this->inverseJoinColumns[0]->name, ConstraintMode::PROVIDER_DEFAULT);
        }
    }
}