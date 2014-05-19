<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Util;

use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

/**
 * Generate and parse product value key
 *
 * @see PimEnterprise\Bundle\WorkflowBundle\Serializer\ProductNormalizer
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductValueKeyGenerator
{
    const CODE = 0;
    const LOCALE = 1;
    const SCOPE = 2;

    /**
     * Generate a value key
     *
     * @param AbstractProductValue $value
     *
     * @return string
     */
    public function generate(AbstractProductValue $value)
    {
        $attribute = $value->getAttribute();
        $key = $attribute->getCode();

        if ($attribute->isLocalizable()) {
            $key .= '-' . $value->getLocale();
        }

        if ($attribute->isScopable()) {
            if (!$attribute->isLocalizable()) {
                $key .= '-';
            }
            $key .= '-' . $value->getScope();
        }

        return $key;
    }

    /**
     * Get a specific part of a key
     *
     * @param string  $key
     * @param integer $part one of self::CODE, self::LOCALE or self::SCOPE
     *
     * @return null|string
     */
    public function getPart($key, $part)
    {
        if (!in_array($part, [self::CODE, self::LOCALE, self::SCOPE], true)) {
            throw new \InvalidArgumentException(
                sprintf('Unknown key part "%s"', $part)
            );
        }

        $parts = explode('-', $key);

        return isset($parts[$part]) && !empty($parts[$part]) ? $parts[$part] : null;
    }
}
