<?php
declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Common\Fake;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\FindAttributeNextOrderInterface;

class InMemoryFindAttributeNextOrder implements FindAttributeNextOrderInterface
{
    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    public function __construct(InMemoryAttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function forEnrichedEntity(EnrichedEntityIdentifier $enrichedEntityIdentifier): int
    {
        /** @var AbstractAttribute[] $attributes */
        $attributes = $this->attributeRepository->findByEnrichedEntity($enrichedEntityIdentifier);

        $maxOrder = 0;
        foreach ($attributes as $attribute) {
            $attributeOrder = $attribute->getOrder()->intValue();
            $maxOrder = $attributeOrder > $maxOrder ? $attributeOrder : $maxOrder;
        }

        return count($attributes) === 0 ? 0 : ($maxOrder + 1);
    }
}
