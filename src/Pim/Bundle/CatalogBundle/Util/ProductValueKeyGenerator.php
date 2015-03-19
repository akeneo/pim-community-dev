<?php

namespace Pim\Bundle\CatalogBundle\Util;

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Product value key generator
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueKeyGenerator
{
    /**
     * This class is not intended to be instanciated
     */
    private function __construct()
    {
    }

    /**
     * Get the internal key that is used to index
     * a product value in a collection of values
     *
     * @param ProductValueInterface $value
     *
     * @return string
     */
    public static function getKey(ProductValueInterface $value, $separator = '-')
    {
        $attribute = $value->getAttribute();
        $key = $attribute->getCode();
        if ($attribute->isLocalizable()) {
            $key .= $separator . $value->getLocale();
        }
        if ($attribute->isScopable()) {
            $key .= $separator . $value->getScope();
        }

        return $key;
    }
}
