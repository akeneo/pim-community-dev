<?php

namespace Pim\Bundle\CatalogBundle\Updater;

/**
 * Invalid argument exceptions the updater can throw when performing an action.
 *
 * @author    Julien Janvier <julien.jjanvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidArgumentException extends \InvalidArgumentException
{
    /**
     * @param string $attribute
     * @param string $expected
     * @param string $action
     * @param string $type
     *
     * @return InvalidArgumentException
     */
    public static function expected($attribute, $expected, $action, $type)
    {
        return new self(
            sprintf('Attribute "%s" expects %s as data (for %s %s).', $attribute, $expected, $action, $type)
        );
    }

    /**
     * @param string $attribute
     * @param string $action
     * @param string $type
     *
     * @return InvalidArgumentException
     */
    public static function booleanExpected($attribute, $action, $type)
    {
        return new self(
            sprintf('Attribute "%s" expects a boolean as data (for %s %s).', $attribute, $action, $type)
        );
    }

    /**
     * @param string $attribute
     * @param string $action
     * @param string $type
     *
     * @return InvalidArgumentException
     */
    public static function floatExpected($attribute, $action, $type)
    {
        return new self(
            sprintf('Attribute "%s" expects a float as data (for %s %s).', $attribute, $action, $type)
        );
    }

    /**
     * @param string $attribute
     * @param string $action
     * @param string $type
     *
     * @return InvalidArgumentException
     */
    public static function integerExpected($attribute, $action, $type)
    {
        return new self(
            sprintf('Attribute "%s" expects an integer as data (for %s %s).', $attribute, $action, $type)
        );
    }

    /**
     * @param string $attribute
     * @param string $action
     * @param string $type
     *
     * @return InvalidArgumentException
     */
    public static function numericExpected($attribute, $action, $type)
    {
        return new self(
            sprintf('Attribute "%s" expects a numeric as data (for %s %s).', $attribute, $action, $type)
        );
    }

    /**
     * @param string $attribute
     * @param string $action
     * @param string $type
     *
     * @return InvalidArgumentException
     */
    public static function stringExpected($attribute, $action, $type)
    {
        return new self(
            sprintf('Attribute "%s" expects a string as data (for %s %s).', $attribute, $action, $type)
        );
    }

    /**
     * @param string $attribute
     * @param string $action
     * @param string $type
     *
     * @return InvalidArgumentException
     */
    public static function arrayExpected($attribute, $action, $type)
    {
        return new self(
            sprintf('Attribute "%s" expects an array as data (for %s %s).', $attribute, $action, $type)
        );
    }

    /**
     * @param string $attribute
     * @param string $action
     * @param string $type
     *
     * @return InvalidArgumentException
     */
    public static function arrayOfArraysExpected($attribute, $action, $type)
    {
        return new self(
            sprintf('Attribute "%s" expects an array of arrays as data (for %s %s).', $attribute, $action, $type)
        );
    }

    /**
     * @param string $attribute
     * @param string $key
     * @param string $action
     * @param string $type
     *
     * @return InvalidArgumentException
     */
    public static function arrayKeyExpected($attribute, $key, $action, $type)
    {
        return new self(
            sprintf(
                'Attribute "%s" expects an array with the key "%s" as data (for %s %s).',
                $attribute,
                $key,
                $action,
                $type
            )
        );
    }

    /**
     * @param string $attribute
     * @param string $key
     * @param string $because
     * @param string $action
     * @param string $type
     *
     * @return InvalidArgumentException
     */
    public static function arrayInvalidKey($attribute, $key, $because, $action, $type)
    {
        return new self(
            sprintf(
                'Attribute "%s" expects an array with valid data for the key "%s". %s (for %s %s).',
                $attribute,
                $key,
                $because,
                $action,
                $type
            )
        );
    }

    /**
     * @param string $attribute
     * @param string $key
     * @param string $action
     * @param string $type
     *
     * @return InvalidArgumentException
     */
    public static function arrayNumericKeyExpected($attribute, $key, $action, $type)
    {
        return new self(
            sprintf(
                'Attribute "%s" expects an array with numeric data for the key "%s" (for %s %s).',
                $attribute,
                $key,
                $action,
                $type
            )
        );
    }

    /**
     * @param string $attribute
     * @param string $key
     * @param string $action
     * @param string $type
     *
     * @return InvalidArgumentException
     */
    public static function arrayStringKeyExpected($attribute, $key, $action, $type)
    {
        return new self(
            sprintf(
                'Attribute "%s" expects an array with string data for the key "%s" (for %s %s).',
                $attribute,
                $key,
                $action,
                $type
            )
        );
    }
}
