<?php

namespace Pim\Component\Catalog\Exception;

/**
 * Invalid argument exceptions the updater can throw when performing an action.
 *
 * @author    Julien Janvier <julien.jjanvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidArgumentException extends \InvalidArgumentException
{
    const EXPECTED_CODE = 100;
    const BOOLEAN_EXPECTED_CODE = 101;
    const FLOAT_EXPECTED_CODE = 102;
    const INTEGER_EXPECTED_CODE = 103;
    const NUMERIC_EXPECTED_CODE = 104;
    const STRING_EXPECTED_CODE = 105;
    const ARRAY_EXPECTED_CODE = 106;
    const ARRAY_OF_ARRAYS_EXPECTED_CODE = 107;

    const ARRAY_KEY_EXPECTED_CODE = 200;
    const ARRAY_INVALID_KEY_CODE = 201;
    const ARRAY_NUMERIC_KEY_EXPECTED_CODE = 202;
    const ARRAY_STRING_KEY_EXPECTED_CODE = 203;
    const ARRAY_STRING_VALUE_EXPECTED_CODE = 204;
    const EMPTY_ARRAY_CODE = 205;

    const VALID_ENTITY_CODE_EXPECTED_CODE = 300;
    const LOCALE_AND_SCOPE_EXPECTED_CODE = 301;
    const SCOPE_EXPECTED_CODE = 302;
    const ASSOCIATION_FORMAT_EXPECTED_CODE = 303;

    /**
     * @param string $name
     * @param string $expected
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function expected($name, $expected, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute or field "%s" expects %s as data, "%s" given (for %s %s).',
                $name,
                $expected,
                $data,
                $action,
                $type
            ),
            self::EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function booleanExpected($name, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute or field "%s" expects a boolean as data, "%s" given (for %s %s).',
                $name,
                $data,
                $action,
                $type
            ),
            self::BOOLEAN_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function floatExpected($name, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute or field "%s" expects a float as data, "%s" given (for %s %s).',
                $name,
                $data,
                $action,
                $type
            ),
            self::FLOAT_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function integerExpected($name, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute or field "%s" expects an integer as data, "%s" given (for %s %s).',
                $name,
                $data,
                $action,
                $type
            ),
            self::INTEGER_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function numericExpected($name, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute or field "%s" expects a numeric as data, "%s" given (for %s %s).',
                $name,
                $data,
                $action,
                $type
            ),
            self::NUMERIC_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function stringExpected($name, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute or field "%s" expects a string as data, "%s" given (for %s %s).',
                $name,
                $data,
                $action,
                $type
            ),
            self::STRING_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayExpected($name, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute or field "%s" expects an array as data, "%s" given (for %s %s).',
                $name,
                $data,
                $action,
                $type
            ),
            self::ARRAY_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayOfArraysExpected($name, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute or field "%s" expects an array of arrays as data, "%s" given (for %s %s).',
                $name,
                $data,
                $action,
                $type
            ),
            self::ARRAY_OF_ARRAYS_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $key
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayKeyExpected($name, $key, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute or field "%s" expects an array with the key "%s" as data, "%s" given (for %s %s).',
                $name,
                $key,
                $data,
                $action,
                $type
            ),
            self::ARRAY_KEY_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $key
     * @param string $because
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayInvalidKey($name, $key, $because, $action, $type, $data)
    {
        $err = 'Attribute or field "%s" expects an array with valid data for the key "%s". %s, "%s" given (for %s %s).';

        return new self(
            sprintf($err, $name, $key, $because, $data, $action, $type),
            self::ARRAY_INVALID_KEY_CODE
        );
    }

    /**
     * @param string $name
     * @param string $key
     * @param string $because
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function validEntityCodeExpected($name, $key, $because, $action, $type, $data)
    {
        $err = 'Attribute or field "%s" expects a valid %s. %s, "%s" given (for %s %s).';

        return new self(
            sprintf($err, $name, $key, $because, $data, $action, $type),
            self::VALID_ENTITY_CODE_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $key
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayNumericKeyExpected($name, $key, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute or field "%s" expects an array with numeric data for the key "%s", "%s" given (for %s %s).',
                $name,
                $key,
                $data,
                $action,
                $type
            ),
            self::ARRAY_NUMERIC_KEY_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $key
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayStringKeyExpected($name, $key, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute or field "%s" expects an array with string data for the key "%s", "%s" given (for %s %s).',
                $name,
                $key,
                $data,
                $action,
                $type
            ),
            self::ARRAY_STRING_KEY_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $key
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayStringValueExpected($name, $key, $action, $type, $data)
    {
        return new self(
            sprintf(
                'Attribute or field "%s" expects an array with a string value for the key "%s", '.
                '"%s" given (for %s %s).',
                $name,
                $key,
                $data,
                $action,
                $type
            ),
            self::ARRAY_STRING_VALUE_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     *
     * @return InvalidArgumentException
     */
    public static function emptyArray($name)
    {
        return new self(
            sprintf('Attribute or field "%s" expects a non empty array.', $name),
            self::EMPTY_ARRAY_CODE
        );
    }

    /**
     * @param string $name
     * @param string $action
     * @param string $type
     *
     * @return InvalidArgumentException
     */
    public static function localeAndScopeExpected($name, $action, $type)
    {
        return new self(
            sprintf(
                'Attribute or field "%s" expects a valid scope and locale (for %s %s).',
                $name,
                $action,
                $type
            ),
            self::LOCALE_AND_SCOPE_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $action
     * @param string $type
     *
     * @return InvalidArgumentException
     */
    public static function scopeExpected($name, $action, $type)
    {
        return new self(
            sprintf(
                'Attribute or field "%s" expects a valid scope (for %s %s).',
                $name,
                $action,
                $type
            ),
            self::SCOPE_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param array  $data
     *
     * @return InvalidArgumentException
     */
    public static function associationFormatExpected($name, $data)
    {
        return new self(
            sprintf(
                'Attribute or field "%s" expects a valid association format as ["associationTypeCode1" => '.
                '["products" => ["sku1, "sku2"], "groups" => ["group1"]]]", "%s" given.',
                $name,
                print_r($data, true)
            ),
            self::ASSOCIATION_FORMAT_EXPECTED_CODE
        );
    }

    /**
     * @param \Exception $exception
     * @param string     $name
     * @param string     $action
     * @param string     $type
     *
     * @return InvalidArgumentException
     */
    public static function expectedFromPreviousException(\Exception $exception, $name, $action, $type)
    {
        return new self(
            sprintf(
                'Attribute or field "%s" expects valid data, scope and locale (for %s %s). %s',
                $name,
                $action,
                $type,
                $exception->getMessage()
            ),
            $exception->getCode(),
            $exception
        );
    }
}
