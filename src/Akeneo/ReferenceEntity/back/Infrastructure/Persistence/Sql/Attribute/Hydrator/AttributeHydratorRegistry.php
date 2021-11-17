<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeHydratorRegistry
{
    /** @var AttributeHydratorInterface[] */
    private array $attributeHydrators = [];

    public function register(AttributeHydratorInterface $attributeHydrator): void
    {
        $this->attributeHydrators[] = $attributeHydrator;
    }

    public function getHydrator(array $toHydrate): AttributeHydratorInterface
    {
        foreach ($this->attributeHydrators as $attributeHydrator) {
            if ($attributeHydrator->supports($toHydrate)) {
                return $attributeHydrator;
            }
        }

        throw new \RuntimeException('There was no attribute hydrator found for the given result');
    }
}
