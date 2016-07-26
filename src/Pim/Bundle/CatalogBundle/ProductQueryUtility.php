<?php

namespace Pim\Bundle\CatalogBundle;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

/**
 * Provides util methods to ease the query building in MongoDB
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductQueryUtility
{
    /** @staticvar string */
    const FIELD_TOKEN_SEPARATOR = '-';

    /** @staticvar string */
    const ELEMENT_TOKEN_SEPARATOR = '.';

    /** @staticvar string */
    const NORMALIZED_FIELD = 'normalizedData';

    /**
     * Normalize the field name from attribute and catalog context
     *
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $scope
     *
     * @return string
     */
    public static function getNormalizedValueFieldFromAttribute(
        AttributeInterface $attribute,
        $locale = null,
        $scope = null
    ) {
        if ($attribute->isLocalizable() && null === $locale) {
            throw new \LogicException('Locale is not configured');
        }
        if ($attribute->isScopable() && null === $scope) {
            throw new \LogicException('Scope is not configured');
        }

        return self::getNormalizedValueField(
            $attribute->getCode(),
            $attribute->isLocalizable(),
            $attribute->isScopable(),
            ($attribute->isLocalizable() ? $locale : null),
            ($attribute->isScopable() ? $scope : null)
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
     * @param string $attributeCode
     * @param bool   $localizable
     * @param bool   $scopable
     * @param string $locale
     * @param string $scope
     *
     * @throws \LogicException
     *
     * @return string
     */
    public static function getNormalizedValueField(
        $attributeCode,
        $localizable,
        $scopable,
        $locale = null,
        $scope = null
    ) {
        $suffix = '';

        if ($localizable) {
            if (null === $locale) {
                throw new \LogicException('Locale is not configured');
            }
            $suffix = sprintf(self::FIELD_TOKEN_SEPARATOR.'%s', $locale);
        }
        if ($scopable) {
            if (null === $scope) {
                throw new \LogicException('Scope is not configured');
            }
            $suffix .= sprintf(self::FIELD_TOKEN_SEPARATOR.'%s', $scope);
        }

        return $attributeCode.$suffix;
    }
}
