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
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyIsLinkedToAtLeastOneProductAttributeInterface;
use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository as InMemoryProductAttributeRepository;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class InMemoryAssetFamilyIsLinkedToAtLeastOneProductAttribute implements AssetFamilyIsLinkedToAtLeastOneProductAttributeInterface
{
    private InMemoryProductAttributeRepository $inMemoryAttributeRepository;

    public function __construct(InMemoryProductAttributeRepository $inMemoryAttributeRepository)
    {
        $this->inMemoryAttributeRepository = $inMemoryAttributeRepository;
    }

    public function isLinked(AssetFamilyIdentifier $identifier): bool
    {
        $attributes = $this->inMemoryAttributeRepository->findBy([
            'attributeType' => AssetCollectionType::ASSET_COLLECTION,
        ]);

        $linkedAssets = [];
        /** @var AttributeInterface $attribute */
        foreach ($attributes as $attribute) {
            $linkedAssets[] = $attribute->getProperty('reference_data_name');
        }

        return in_array((string) $identifier, array_filter(array_unique($linkedAssets)));
    }
}
