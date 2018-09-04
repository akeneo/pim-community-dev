<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\HydratorInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeHydratorRegistry
{
    /** @var HydratorInterface */
    private $attributeHydrators;

    public function __construct()
    {
        $this->attributeHydrators = [];
    }

    public function register(HydratorInterface $attributeHydrator): void
    {
        $this->attributeHydrators[] = $attributeHydrator;
    }

    public function getHydrator(array $toHydrate): HydratorInterface
    {
        foreach ($this->attributeHydrators as $attributeHydrator) {
            if ($attributeHydrator->supports($toHydrate)) {
                return $attributeHydrator;
            }
        }

        throw new \RuntimeException('There was no attribute hydrator found for the given result');
    }
}
