<?php

namespace Pim\Bundle\CatalogBundle\Filter;

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Filter the product values according to channel codes provided in options.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueChannelFilter extends AbstractFilter implements CollectionFilterInterface, ObjectFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filterObject($productValue, $type, array $options = [])
    {
        if (!$productValue instanceof ProductValueInterface) {
            throw new \LogicException('This filter only handles objects of type "ProductValueInterface"');
        }

        $channelCodes = isset($options['channels']) ? $options['channels'] : [];
        $attribute    = $productValue->getAttribute();

        return !empty($channelCodes) &&
            $attribute->isScopable() &&
            !in_array($productValue->getScope(), $channelCodes);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return $object instanceof ProductValueInterface;
    }
}
