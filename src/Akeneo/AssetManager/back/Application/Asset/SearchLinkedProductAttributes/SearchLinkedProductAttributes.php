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

namespace Akeneo\AssetManager\Application\Asset\SearchLinkedProductAttributes;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetAttributeTranslations;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 */
class SearchLinkedProductAttributes
{
    public function __construct(
        private GetAttributes $getAttributes,
        private GetAttributeTranslations $getAttributeTranslations
    ) {
    }

    /**
     * @return LinkedProductAttribute[]
     */
    public function searchByAssetFamilyIdentifier(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        $productAttributes = $this->getAttributes->forType('pim_catalog_asset_collection');

        $filteredProductAttributes = array_filter($productAttributes, function (Attribute $attribute) use ($assetFamilyIdentifier) {
            return $attribute->properties()['reference_data_name'] === (string) $assetFamilyIdentifier;
        });

        $labels = $this->getAttributeTranslations->byAttributeCodes(array_keys($filteredProductAttributes));

        return array_map(function (Attribute $attribute) use ($labels) {
            return new LinkedProductAttribute(
                $attribute->code(),
                $attribute->type(),
                $labels[$attribute->code()] ?? [],
                $attribute->properties()['reference_data_name'],
                $attribute->useableAsGridFilter()
            );
        }, array_values($filteredProductAttributes));
    }
}
