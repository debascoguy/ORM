<?php

namespace Emma\ORM\Attributes\Repository;

use Attribute;
use Emma\Di\Container\ContainerManager;
use Emma\ORM\Attributes\Entity\Entity;
use Emma\ORM\EntityManager\Utils\AttributeHandler;

#[Attribute(Attribute::TARGET_CLASS)]
final class Repository
{
    use ContainerManager;

    private Entity $entityAttribute;

    /**
     * @throws \ReflectionException|\InvalidArgumentException
     */
    public function __construct(public string $targetEntity)
    {
        /** @var AttributeHandler $attributeHandler */
        $attributeHandler = $this->getContainer()->get(AttributeHandler::class);
        /** @var \ReflectionAttribute[] $entityAttributes */
        $entityAttributes = [];
        if (!$attributeHandler->isEntity($this->targetEntity, $entityAttributes))
        {
            throw new \InvalidArgumentException("Invalid Entity {$targetEntity} Passed For Repository Attributes");
        }
        $this->entityAttribute = $entityAttributes[0]->newInstance();
    }

    /**
     * @return Entity
     */
    public function getEntityAttribute(): Entity
    {
        return $this->entityAttribute;
    }
}