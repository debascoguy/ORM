<?php

namespace Emma\ORM\Attributes\Relationships;

use Attribute;
use Emma\ORM\Constants\CascadeType;
use Emma\ORM\Constants\FetchType;
use Emma\ORM\Constants\Relation;

#[Attribute(Attribute::TARGET_PROPERTY)]
class OneToOne extends RelationshipType
{
    /**
     * @param string $targetEntity
     * @param array $cascade
     * @param string $fetch
     * @param bool $optional
     * @param string $mappedBy
     * @throws \ReflectionException
     */
    public function __construct(
        public string $targetEntity, /** @default to Property Datatype Entity::Class */
        public array $cascade = [CascadeType::ALL],
        public string $fetch = FetchType::FETCH_EAGER, /**  @default recommended FETCH_TYPE based on ENTITY RELATION */
        public bool $optional = true,
        public string $mappedBy = ""
    ) {
        parent::__construct(Relation::ONE_TO_ONE, $this->targetEntity, $this->cascade, $this->fetch);
    }
}