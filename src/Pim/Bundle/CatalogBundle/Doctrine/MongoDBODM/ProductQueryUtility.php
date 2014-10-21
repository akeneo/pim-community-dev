<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
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
     * @param array             $context
     *
     * @return string
     */
    public static function getNormalizedValueFieldFromAttribute(AttributeInterface $attribute, array $context)
    {
        return self::getNormalizedValueField(
            $attribute->getCode(),
            $attribute->isLocalizable(),
            $attribute->isScopable(),
            ($attribute->isLocalizable() ? $context['locale'] : null),
            ($attribute->isScopable() ? $context['scope'] : null)
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
     *
     * @throws \LogicException
     */
    public static function getNormalizedValueField($attributeCode, $localizable, $scopable, $locale, $scope)
    {
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
