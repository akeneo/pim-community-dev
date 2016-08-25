<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Enrich\Provider\Filter;

use Pim\Bundle\EnrichBundle\Provider\Filter\FilterProviderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use PimEnterprise\Bundle\ProductAssetBundle\AttributeType\AttributeTypes;

/**
 * Filter provider for asset collections
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class AssetCollectionFilterProvider implements FilterProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFilter($attribute)
    {
        return ['product-export-builder' => 'akeneo-attribute-select-reference-data-filter'];
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
