<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

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
     * This class is not intended to be instantiated
     */
    private function __construct()
    {
    }

    /**
     * Get the internal key that is used to index
     * a product value in a collection of values
     *
     * @param ValueInterface $value
     * @param string         $separator
     *
     * @return string
     */
    public static function getKey(ValueInterface $value, $separator = '-')
    {
        $key = $value->getAttributeCode();

        if ($attribute->isLocalizable()) {
            $key .= $separator . $value->getLocale();
        }
        if ($attribute->isScopable()) {
            $key .= $separator . $value->getScope();
        }

        return $key;
    }
}
