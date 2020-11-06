<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Exception;

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

    /** @var string */
    protected $className;

    /**
     * @param string          $className
     * @param string          $message
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct($className, $message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->className = $className;
    }

    /**
     * @param string $name
     * @param string $expected
     * @param string $className
     * @param string $data
     */
    public static function expected(string $name, string $expected, string $className, string $data): \Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects %s as data, "%s" given.',
                $name,
                $expected,
                $data
            ),
            self::EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $className
     * @param string $data
     */
    public static function booleanExpected(string $name, string $className, string $data): \Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects a boolean as data, "%s" given.',
                $name,
                $data
            ),
            self::BOOLEAN_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $className
     * @param string $data
     */
    public static function floatExpected(string $name, string $className, string $data): \Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects a float as data, "%s" given.',
                $name,
                $data
            ),
            self::FLOAT_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $className
     * @param string $data
     */
    public static function integerExpected(string $name, string $className, string $data): \Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects an integer as data, "%s" given.',
                $name,
                $data
            ),
            self::INTEGER_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $className
     * @param string $data
     */
    public static function numericExpected(string $name, string $className, string $data): \Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects a numeric as data, "%s" given.',
                $name,
                $data
            ),
            self::NUMERIC_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $className
     * @param string $data
     */
    public static function stringExpected(string $name, string $className, string $data): \Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects a string as data, "%s" given.',
                $name,
                $data
            ),
            self::STRING_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $className
     * @param string $data
     */
    public static function arrayExpected(string $name, string $className, string $data): \Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects an array as data, "%s" given.',
                $name,
                $data
            ),
            self::ARRAY_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $className
     * @param string $data
     */
    public static function arrayOfArraysExpected(string $name, string $className, string $data): \Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects an array of arrays as data, "%s" given.',
                $name,
                $data
            ),
            self::ARRAY_OF_ARRAYS_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $key
     * @param string $className
     * @param string $data
     */
    public static function arrayKeyExpected(string $name, string $key, string $className, string $data): \Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects an array with the key "%s", "%s" given.',
                $name,
                $key,
                $data
            ),
            self::ARRAY_KEY_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $key
     * @param string $because
     * @param string $className
     * @param string $data
     */
    public static function arrayInvalidKey(string $name, string $key, string $because, string $className, string $data): \Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException
    {
        $err = 'Attribute or field "%s" expects an array with valid data for the key "%s". %s, "%s" given.';

        return new self(
            $className,
            sprintf($err, $name, $key, $because, $data),
            self::ARRAY_INVALID_KEY_CODE
        );
    }

    /**
     * @param string $name
     * @param string $key
     * @param string $because
     * @param string $className
     * @param string $data
     */
    public static function validEntityCodeExpected(string $name, string $key, string $because, string $className, string $data): \Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException
    {
        $err = 'Attribute or field "%s" expects a valid %s. %s, "%s" given.';

        return new self(
            $className,
            sprintf($err, $name, $key, $because, $data),
            self::VALID_ENTITY_CODE_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $key
     * @param string $className
     * @param string $data
     */
    public static function arrayNumericKeyExpected(string $name, string $key, string $className, string $data): \Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects an array with numeric data for the key "%s", "%s" given.',
                $name,
                $key,
                $data
            ),
            self::ARRAY_NUMERIC_KEY_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $key
     * @param string $className
     * @param string $data
     */
    public static function arrayStringKeyExpected(string $name, string $key, string $className, string $data): \Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects an array with string data for the key "%s", "%s" given.',
                $name,
                $key,
                $data
            ),
            self::ARRAY_STRING_KEY_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $key
     * @param string $className
     * @param string $data
     */
    public static function arrayStringValueExpected(string $name, string $key, string $className, string $data): \Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects an array with a string value for the key "%s", "%s" given.',
                $name,
                $key,
                $data
            ),
            self::ARRAY_STRING_VALUE_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     */
    public static function emptyArray(string $name): \Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException
    {
        return new self(
            null,
            sprintf('Attribute or field "%s" expects a non empty array.', $name),
            self::EMPTY_ARRAY_CODE
        );
    }

    /**
     * @param string $name
     * @param string $className
     */
    public static function localeAndScopeExpected(string $name, string $className): \Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects a valid scope and locale.',
                $name
            ),
            self::LOCALE_AND_SCOPE_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param string $className
     */
    public static function scopeExpected(string $name, string $className): \Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects a valid scope.',
                $name
            ),
            self::SCOPE_EXPECTED_CODE
        );
    }

    /**
     * @param string $name
     * @param array  $data
     */
    public static function associationFormatExpected(string $name, array $data): \Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException
    {
        return new self(
            null,
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
     * @param string     $className
     */
    public static function expectedFromPreviousException(\Exception $exception, string $name, string $className): \Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException
    {
        return new self(
            $className,
            sprintf(
                'Attribute or field "%s" expects valid data, scope and locale. %s',
                $name,
                $exception->getMessage()
            ),
            $exception->getCode(),
            $exception
        );
    }
}
