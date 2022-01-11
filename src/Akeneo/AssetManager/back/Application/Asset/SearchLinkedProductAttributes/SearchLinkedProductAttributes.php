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
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 */
class SearchLinkedProductAttributes
{
    public function __construct(
        private GetAttributes $getAttributes
    ){
    }

    /**
     * @return Attribute[]
     */
    public function searchByAssetFamilyIdentifier(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        $productAttributes = $this->getAttributes->forType('pim_catalog_asset_collection');

        return array_filter($productAttributes, function($attribute) use ($assetFamilyIdentifier) {
            return $attribute->properties()['reference_data_name'] === (string) $assetFamilyIdentifier;
        });
    }
}
