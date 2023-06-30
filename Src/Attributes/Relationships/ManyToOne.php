<?php

namespace Emma\ORM\Attributes\Relationships;

use Attribute;
use Emma\ORM\Constants\FetchType;
use Emma\ORM\Constants\Relation;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ManyToOne extends RelationshipType
{
    /**
     * @param string $targetEntity
     * @param array $cascade
     * @param string $fetch
     * @param bool $optional
     * @throws \ReflectionException
     */
    public function __construct(
        public string $targetEntity, /** @default to Property Datatype Entity::Class */
        public array $cascade = [], /** CascadeType  */
        public string $fetch = FetchType::FETCH_EAGER,  /** FETCH_LAZY for Advanced User OR As Needed */
        public bool $optional = true
    ) {
        parent::__construct(Relation::MANY_TO_ONE, $this->targetEntity, $this->cascade, $this->fetch);
    }

}