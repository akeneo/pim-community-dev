<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Catalog\Normalizer\Standard;

use Akeneo\Asset\Bundle\AttributeType\AttributeTypes;
use Pim\Component\Catalog\Normalizer\Standard\Product\ProductValueNormalizer as BaseProductValueNormalizer;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ProductValueNormalizer extends BaseProductValueNormalizer
{
    /**
     * {@inheritdoc}
     */
    protected function sortData(array $data, string $attributeType): array
    {
        if (AttributeTypes::ASSETS_COLLECTION === $attributeType) {
            return $data;
        }

        return parent::sortData($data, $attributeType);
    }
}
