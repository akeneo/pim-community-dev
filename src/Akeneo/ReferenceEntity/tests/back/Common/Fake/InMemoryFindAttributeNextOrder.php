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

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributeNextOrderInterface;

class InMemoryFindAttributeNextOrder implements FindAttributeNextOrderInterface
{
    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    public function __construct(InMemoryAttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function withReferenceEntityIdentifier(ReferenceEntityIdentifier $referenceEntityIdentifier): AttributeOrder
    {
        /** @var AbstractAttribute[] $attributes */
        $attributes = $this->attributeRepository->findByReferenceEntity($referenceEntityIdentifier);

        $maxOrder = 0;
        foreach ($attributes as $attribute) {
            $attributeOrder = $attribute->getOrder()->intValue();
            $maxOrder = $attributeOrder > $maxOrder ? $attributeOrder : $maxOrder;
        }

        return count($attributes) === 0 ? AttributeOrder::fromInteger(0) : AttributeOrder::fromInteger($maxOrder + 1);
    }
}
