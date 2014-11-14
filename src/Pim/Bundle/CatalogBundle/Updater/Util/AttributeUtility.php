<?php

namespace Pim\Bundle\CatalogBundle\Updater\Util;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * AttributeUtility
 *
 * TODO : need to be merged with other attribute utility method, change naming too
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeUtility
{
    /**
     * Check if locale data is consistent with the attribute localizable property
     *
     * @param AttributeInterface $attribute
     * @param string             $locale
     *
     * @throws \LogicException
     */
    public static function validateLocale(AttributeInterface $attribute, $locale)
    {
        // TODO : check the existence of locale in DB
        if ($attribute->isLocalizable() && null === $locale) {
            throw new \LogicException(
                sprintf(
                    'Locale is expected for the attribute "%s"',
                    $attribute->getCode()
                )
            );
        }
        if (!$attribute->isLocalizable() && null !== $locale) {
            throw new \LogicException(
                sprintf(
                    'Locale is not expected for the attribute "%s"',
                    $attribute->getCode()
                )
            );
        }
    }

    /**
     * Check if metric family of attribute are the same
     *
     * @param AttributeInterface $fromAttribute
     * @param AttributeInterface $toAttribute
     */
    public static function validateUnitFamilyFromAttribute(
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute
    ) {
        if ($fromAttribute->getMetricFamily() !== $toAttribute->getMetricFamily()) {
            throw new \LogicException(
                sprintf(
                    'Metric families are not the same for attributes: "%s and %s"',
                    $fromAttribute->getCode(),
                    $toAttribute->getCode()
                )
            );
        }
    }

    /**
     * Check if scope data is consistent with the attribute scopable property
     *
     * @param AttributeInterface $attribute
     * @param string             $scope
     *
     * @throws \LogicException
     */
    public static function validateScope(AttributeInterface $attribute, $scope)
    {
        // TODO : check the existence of scope in DB
        if ($attribute->isScopable() && null === $scope) {
            throw new \LogicException(
                sprintf(
                    'Scope is expected for the attribute "%s"',
                    $attribute->getCode()
                )
            );
        }
        if (!$attribute->isScopable() && null !== $scope) {
            throw new \LogicException(
                sprintf(
                    'Scope is not expected for the attribute "%s"',
                    $attribute->getCode()
                )
            );
        }
    }
}
