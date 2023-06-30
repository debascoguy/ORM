<?php

namespace Emma\ORM\Attributes\Relationships;

use Attribute;
use Emma\Di\Container\ContainerManager;
use Emma\ORM\Attributes\Entity\Entity;
use Emma\ORM\Constants\FetchType;
use Emma\ORM\EntityManager\Utils\AttributeHandler;

#[Attribute(Attribute::TARGET_PROPERTY)]
class RelationshipType extends Relationship
{
    use ContainerManager;

    protected Entity $targetEntityAttribute;

    /**
     * @param string $type
     * @param string $targetEntity
     * @throws \ReflectionException
     */
    public function __construct(
        private string $type,
        public string $targetEntity, /** @default to Property Datatype Entity::Class */
        public array $cascade = [], /** CascadeType  */
        /** FETCH_LAZY for Advanced User ONLY or MANY_TO_MANY | ONE_TO_MANY relationship */
        public string $fetch = FetchType::FETCH_EAGER
    ) {
        /** @var AttributeHandler $attributeHandler */
        $attributeHandler = $this->getContainer()->get(AttributeHandler::class);
        /** @var \ReflectionAttribute[] $entityAttributes */
        $entityAttributes = [];
        if (!$attributeHandler->isEntity($targetEntity, $entityAttributes))
        {
            $className = get_class($this);
            throw new \InvalidArgumentException("Invalid Target Entity {$targetEntity} Passed For {$className} Attributes");
        }
        /** @var Entity $entityAttributes entityAttribute */
        $this->targetEntityAttribute = $entityAttributes[0]->newInstance();
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTargetEntity(): string
    {
        return $this->targetEntity;
    }

    /**
     * @return Entity
     */
    public function getTargetEntityAttribute(): Entity
    {
        return $this->targetEntityAttribute;
    }
}