<?php

namespace Pim\Bundle\CatalogBundle\Filter;

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Filter the product values according to locale codes provided in options.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueLocaleFilter extends AbstractFilter implements CollectionFilterInterface, ObjectFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filterObject($productValue, $type, array $options = [])
    {
        if (!$productValue instanceof ProductValueInterface) {
            throw new \LogicException('This filter only handles objects of type "ProductValueInterface"');
        }

        $localeCodes = isset($options['locales']) ? $options['locales'] : [];
        $attribute   = $productValue->getAttribute();

        return !empty($localeCodes) &&
            $attribute->isLocalizable() &&
            !in_array($productValue->getLocale(), $localeCodes);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return $object instanceof ProductValueInterface;
    }
}
