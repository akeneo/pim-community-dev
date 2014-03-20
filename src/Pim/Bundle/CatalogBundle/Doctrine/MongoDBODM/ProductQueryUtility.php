<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
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
    public static function getNormalizedValueFieldFromAttribute(AbstractAttribute $attribute, $locale, $scope)
    {
        return self::getNormalizedValueField(
            $attribute->getCode(),
            $attribute->isLocalizable(),
            $attribute->isScopable(),
            $locale,
            $scope
        );
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

    /**
     * Normalize the field name from properties
     *
     * @param string  $attributeCode
     * @param boolean $localizable
     * @param boolean $scopable
     * @param string  $locale
     * @param string  $scope
     *
     * @return string
     */
    public static function getNormalizedValueField($attributeCode, $localizable, $scopable, $locale, $scope)
    {
        $suffix = '';

        if ($localizable) {
            $suffix = sprintf(self::FIELD_TOKEN_SEPARATOR.'%s', $locale);
        }
        if ($scopable) {
            $suffix .= sprintf(self::FIELD_TOKEN_SEPARATOR.'%s', $scope);
        }

        return $attributeCode.$suffix;
    }
}
