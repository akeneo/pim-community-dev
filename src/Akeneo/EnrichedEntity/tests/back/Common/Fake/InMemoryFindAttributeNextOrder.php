<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\Common\Fake;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\Attribute\FindAttributeNextOrderInterface;

class InMemoryFindAttributeNextOrder implements FindAttributeNextOrderInterface
{
    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    public function __construct(InMemoryAttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function withEnrichedEntityIdentifier(EnrichedEntityIdentifier $enrichedEntityIdentifier): int
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
