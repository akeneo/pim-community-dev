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
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function expected($attribute, $expected, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute "%s" expects %s as data, "%s" given (for %s %s).',
                $attribute,
                $expected,
                $data,
                $action,
                $type
            )
        );
    }

    /**
     * @param string $attribute
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function booleanExpected($attribute, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute "%s" expects a boolean as data, "%s" given (for %s %s).',
                $attribute,
                $data,
                $action,
                $type
            )
        );
    }

    /**
     * @param string $attribute
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function floatExpected($attribute, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute "%s" expects a float as data, "%s" given (for %s %s).',
                $attribute,
                $data,
                $action,
                $type
            )
        );
    }

    /**
     * @param string $attribute
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function integerExpected($attribute, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute "%s" expects an integer as data, "%s" given (for %s %s).',
                $attribute,
                $data,
                $action,
                $type
            )
        );
    }

    /**
     * @param string $attribute
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function numericExpected($attribute, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute "%s" expects a numeric as data, "%s" given (for %s %s).',
                $attribute,
                $data,
                $action,
                $type
            )
        );
    }

    /**
     * @param string $attribute
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function stringExpected($attribute, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute "%s" expects a string as data, "%s" given (for %s %s).',
                $attribute,
                $data,
                $action,
                $type
            )
        );
    }

    /**
     * @param string $attribute
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayExpected($attribute, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute "%s" expects an array as data, "%s" given (for %s %s).',
                $attribute,
                $data,
                $action,
                $type
            )
        );
    }

    /**
     * @param string $attribute
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayOfArraysExpected($attribute, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute "%s" expects an array of arrays as data, "%s" given (for %s %s).',
                $attribute,
                $data,
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
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayKeyExpected($attribute, $key, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute "%s" expects an array with the key "%s" as data, "%s" given (for %s %s).',
                $attribute,
                $key,
                $data,
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
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayInvalidKey($attribute, $key, $because, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute "%s" expects an array with valid data for the key "%s". %s, "%s" given (for %s %s).',
                $attribute,
                $key,
                $because,
                $data,
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
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayNumericKeyExpected($attribute, $key, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute "%s" expects an array with numeric data for the key "%s", "%s" given (for %s %s).',
                $attribute,
                $key,
                $data,
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
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayStringKeyExpected($attribute, $key, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute "%s" expects an array with string data for the key "%s", "%s" given (for %s %s).',
                $attribute,
                $key,
                $data,
                $action,
                $type
            )
        );
    }
}
