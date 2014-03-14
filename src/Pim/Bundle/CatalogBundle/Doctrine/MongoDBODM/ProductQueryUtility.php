<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Provides util methods to ease the query building in MongoDB
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductQueryUtility
{
    /** @var string */
    const FIELD_TOKEN_SEPARATOR = '-';

    /**
     * Normalize the field name from attribute and locale
     *
     * @param AbstractAttribute $attribute
     * @param string            $locale
     * @param string            $scope
     *
     * @return string
     */
    public static function getNormalizedValueField(AbstractAttribute $attribute, $locale, $scope)
    {
        $suffix = '';

        if ($attribute->isLocalizable()) {
            $suffix = sprintf(self::FIELD_TOKEN_SEPARATOR.'%s', $locale);
        }
        if ($attribute->isScopable()) {
            $suffix .= sprintf(self::FIELD_TOKEN_SEPARATOR.'%s', $scope);
        }

        return '.'.$attribute->getCode() . $suffix;
    }

    /**
     * Normalize the field name for value
     *
     * @param ProductValueInterface $value
     *
     * @return string
     */
    public static function getNormalizedValueFieldFromValue(ProductValueInterface $value)
    {
        return self::getNormalizedValueField($value->getAttribute(), $value->getLocale(), $value->getScope());
    }
}
