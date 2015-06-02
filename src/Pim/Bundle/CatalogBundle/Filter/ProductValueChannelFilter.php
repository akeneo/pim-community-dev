<?php

namespace Pim\Bundle\CatalogBundle\Filter;

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Product Value filter
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

        $channels = isset($options['channels']) ? $options['channels'] : [];
        $attribute = $productValue->getAttribute();

        return !empty($channels) &&
            $attribute->isScopable() &&
            !in_array($productValue->getScope(), $channels);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return $object instanceof ProductValueInterface;
    }
}
