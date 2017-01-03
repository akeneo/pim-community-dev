<?php

namespace Pim\Component\Catalog\Exception;

use Akeneo\Component\StorageUtils\Updater\InvalidPropertyException;

// README : this exception already handle a lot of cases you're interested in but does not allow to get the failing
// property, this exception is only used by ProductUpdater and internal pieces related to product updates as Adder,
// Remover, Setter, Copier. Adding the property and making this exception extends the Updater domain exception will
// do the job in a pretty clear way.
//
// Ideally this Exception should be named "InvalidProductArgumentException" or something like that or be located closer
// to the ProductUpdater to make more obvious that this exception is dedicated to the product updater. Not easy to
// change this without broking lot of existing implems and introducing a large BC Break.
//
// Most of static constructors of this exception could become more generic and moved in the parent to be used in other
// updaters (except "localeAndScopeExpected" and "associationFormatExpected" which are product specific).
// The issue to move these static in parent exception is that we say "Attribute or field etc", to do this we should
// change the message to "Property ...", in your PIM language, property is a more generic term, a property can be an
// attribute or a entity field

/**
 * Invalid argument exceptions the updater can throw when performing an action.
 *
 * @author    Julien Janvier <julien.jjanvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvalidArgumentException extends InvalidPropertyException
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
     * Construct the exception. Note: The message is NOT binary safe.
     * @link http://php.net/manual/en/exception.construct.php
     * @param string $message [optional] The Exception message to throw.
     * @param int $code [optional] The Exception code.
     * @param Exception $previous [optional] The previous exception used for the exception chaining. Since 5.3.0
     * @since 5.1.0
     */
    public function __construct($property, $message, $code, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->property = $property;
    }

    /**
     * @param string $property
     * @param string $expected
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function expected($property, $expected, $action, $type, $data)
    {
        return new self(
            $property,
            sprintf(
                'Attribute or field "%s" expects %s as data, "%s" given (for %s %s).',
                $property,
                $expected,
                $data,
                $action,
                $type
            ),
            self::EXPECTED_CODE
        );
    }

    /**
     * @param string $property
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function booleanExpected($property, $action, $type, $data)
    {
        return new self(
            $property,
            sprintf(
                'Attribute or field "%s" expects a boolean as data, "%s" given (for %s %s).',
                $property,
                $data,
                $action,
                $type
            ),
            self::BOOLEAN_EXPECTED_CODE
        );
    }

    /**
     * @param string $property
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function floatExpected($property, $action, $type, $data)
    {
        return new self(
            $property,
            sprintf(
                'Attribute or field "%s" expects a float as data, "%s" given (for %s %s).',
                $property,
                $data,
                $action,
                $type
            ),
            self::FLOAT_EXPECTED_CODE
        );
    }

    /**
     * @param string $property
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function integerExpected($property, $action, $type, $data)
    {
        return new self(
            $property,
            sprintf(
                'Attribute or field "%s" expects an integer as data, "%s" given (for %s %s).',
                $property,
                $data,
                $action,
                $type
            ),
            self::INTEGER_EXPECTED_CODE
        );
    }

    /**
     * @param string $property
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function numericExpected($property, $action, $type, $data)
    {
        return new self(
            $property,
            sprintf(
                'Attribute or field "%s" expects a numeric as data, "%s" given (for %s %s).',
                $property,
                $data,
                $action,
                $type
            ),
            self::NUMERIC_EXPECTED_CODE
        );
    }

    /**
     * @param string $property
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function stringExpected($property, $action, $type, $data)
    {
        return new self(
            $property,
            sprintf(
                'Attribute or field "%s" expects a string as data, "%s" given (for %s %s).',
                $property,
                $data,
                $action,
                $type
            ),
            self::STRING_EXPECTED_CODE
        );
    }

    /**
     * @param string $property
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayExpected($property, $action, $type, $data)
    {
        return new self(
            $property,
            sprintf(
                'Attribute or field "%s" expects an array as data, "%s" given (for %s %s).',
                $property,
                $data,
                $action,
                $type
            ),
            self::ARRAY_EXPECTED_CODE
        );
    }

    /**
     * @param string $property
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayOfArraysExpected($property, $action, $type, $data)
    {
        return new self(
            $property,
            sprintf(
                'Attribute or field "%s" expects an array of arrays as data, "%s" given (for %s %s).',
                $property,
                $data,
                $action,
                $type
            ),
            self::ARRAY_OF_ARRAYS_EXPECTED_CODE
        );
    }

    /**
     * @param string $property
     * @param string $key
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayKeyExpected($property, $key, $action, $type, $data)
    {
        return new self(
            $property,
            sprintf(
                'Attribute or field "%s" expects an array with the key "%s" as data, "%s" given (for %s %s).',
                $property,
                $key,
                $data,
                $action,
                $type
            ),
            self::ARRAY_KEY_EXPECTED_CODE
        );
    }

    /**
     * @param string $property
     * @param string $key
     * @param string $because
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayInvalidKey($property, $key, $because, $action, $type, $data)
    {
        $err = 'Attribute or field "%s" expects an array with valid data for the key "%s". %s, "%s" given (for %s %s).';

        return new self(
            $property,
            sprintf($err, $property, $key, $because, $data, $action, $type),
            self::ARRAY_INVALID_KEY_CODE
        );
    }

    /**
     * @param string $property
     * @param string $key
     * @param string $because
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function validEntityCodeExpected($property, $key, $because, $action, $type, $data)
    {
        $err = 'Attribute or field "%s" expects a valid %s. %s, "%s" given (for %s %s).';

        return new self(
            $property,
            sprintf($err, $property, $key, $because, $data, $action, $type),
            self::VALID_ENTITY_CODE_EXPECTED_CODE
        );
    }

    /**
     * @param string $property
     * @param string $key
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayNumericKeyExpected($property, $key, $action, $type, $data)
    {
        return new self(
            $property,
            sprintf(
                'Attribute or field "%s" expects an array with numeric data for the key "%s", "%s" given (for %s %s).',
                $property,
                $key,
                $data,
                $action,
                $type
            ),
            self::ARRAY_NUMERIC_KEY_EXPECTED_CODE
        );
    }

    /**
     * @param string $property
     * @param string $key
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayStringKeyExpected($property, $key, $action, $type, $data)
    {
        return new self(
            $property,
            sprintf(
                'Attribute or field "%s" expects an array with string data for the key "%s", "%s" given (for %s %s).',
                $property,
                $key,
                $data,
                $action,
                $type
            ),
            self::ARRAY_STRING_KEY_EXPECTED_CODE
        );
    }

    /**
     * @param string $property
     * @param string $key
     * @param string $action
     * @param string $type
     * @param string $data
     *
     * @return InvalidArgumentException
     */
    public static function arrayStringValueExpected($property, $key, $action, $type, $data)
    {
        return new self(
            $property,
            sprintf(
                'Attribute or field "%s" expects an array with a string value for the key "%s", '.
                '"%s" given (for %s %s).',
                $property,
                $key,
                $data,
                $action,
                $type
            ),
            self::ARRAY_STRING_VALUE_EXPECTED_CODE
        );
    }

    /**
     * @param string $property
     *
     * @return InvalidArgumentException
     */
    public static function emptyArray($property)
    {
        return new self(
            $property,
            sprintf('Attribute or field "%s" expects a non empty array.', $property),
            self::EMPTY_ARRAY_CODE
        );
    }

    /**
     * @param string $property
     * @param string $action
     * @param string $type
     *
     * @return InvalidArgumentException
     */
    public static function localeAndScopeExpected($property, $action, $type)
    {
        return new self(
            $property,
            sprintf(
                'Attribute or field "%s" expects a valid scope and locale (for %s %s).',
                $property,
                $action,
                $type
            ),
            self::LOCALE_AND_SCOPE_EXPECTED_CODE
        );
    }

    /**
     * @param string $property
     * @param string $action
     * @param string $type
     *
     * @return InvalidArgumentException
     */
    public static function scopeExpected($property, $action, $type)
    {
        return new self(
            $property,
            sprintf(
                'Attribute or field "%s" expects a valid scope (for %s %s).',
                $property,
                $action,
                $type
            ),
            self::SCOPE_EXPECTED_CODE
        );
    }

    /**
     * @param string $property
     * @param array  $data
     *
     * @return InvalidArgumentException
     */
    public static function associationFormatExpected($property, $data)
    {
        return new self(
            $property,
            sprintf(
                'Attribute or field "%s" expects a valid association format as ["associationTypeCode1" => '.
                '["products" => ["sku1, "sku2"], "groups" => ["group1"]]]", "%s" given.',
                $property,
                print_r($data, true)
            ),
            self::ASSOCIATION_FORMAT_EXPECTED_CODE
        );
    }

    /**
     * @param \Exception $exception
     * @param string     $property
     * @param string     $action
     * @param string     $type
     *
     * @return InvalidArgumentException
     */
    public static function expectedFromPreviousException(\Exception $exception, $property, $action, $type)
    {
        return new self(
            $property,
            sprintf(
                'Attribute or field "%s" expects valid data, scope and locale (for %s %s). %s',
                $property,
                $action,
                $type,
                $exception->getMessage()
            ),
            $exception->getCode(),
            $exception
        );
    }
}
