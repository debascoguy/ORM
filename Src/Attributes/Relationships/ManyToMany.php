<?php

namespace Emma\ORM\Attributes\Relationships;

use Attribute;
use Emma\ORM\Constants\FetchType;
use Emma\ORM\Constants\Relation;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ManyToMany extends RelationshipType
{
    /**
     * @param string $targetEntity
     * @param array $cascade
     * @param string $fetch
     * @param string $mappedBy
     * @throws \ReflectionException
     */
    public function __construct(
        public string $targetEntity, /** @default to Property Datatype Entity::Class */
        public array $cascade = [], /** CascadeType  */
        public string $fetch = FetchType::FETCH_LAZY,
        public string $mappedBy = ""
    ) {
        parent::__construct(Relation::MANY_TO_MANY, $this->targetEntity, $this->cascade, $this->fetch);
    }
}