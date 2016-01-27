<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Enrich\Provider\EmptyValue;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\EnrichBundle\Provider\EmptyValue\EmptyValueProviderInterface;
use PimEnterprise\Bundle\ProductAssetBundle\AttributeType\AttributeTypes;

/**
 * EmptyValue provider for asset collections
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class AssetCollectionEmptyValueProvider implements EmptyValueProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEmptyValue($attribute)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function supports($element)
    {
        return $element instanceof AttributeInterface &&
            AttributeTypes::ASSETS_COLLECTION === $element->getAttributeType();
    }
}
