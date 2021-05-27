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

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributeNextOrderInterface;

class InMemoryFindAttributeNextOrder implements FindAttributeNextOrderInterface
{
    private InMemoryAttributeRepository $attributeRepository;

    public function __construct(InMemoryAttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function withAssetFamilyIdentifier(AssetFamilyIdentifier $assetFamilyIdentifier): AttributeOrder
    {
        /** @var AbstractAttribute[] $attributes */
        $attributes = $this->attributeRepository->findByAssetFamily($assetFamilyIdentifier);

        $maxOrder = 0;
        foreach ($attributes as $attribute) {
            $attributeOrder = $attribute->getOrder()->intValue();
            $maxOrder = $attributeOrder > $maxOrder ? $attributeOrder : $maxOrder;
        }

        return count($attributes) === 0 ? AttributeOrder::fromInteger(0) : AttributeOrder::fromInteger($maxOrder + 1);
    }
}
