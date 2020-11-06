<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query\Filter;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Field filter helper
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FieldFilterHelper
{
    /** @var string */
    const CODE_PROPERTY = 'code';

    /**
     * Get field code part
     *
     * @param string $field
     */
    public static function getCode(string $field): string
    {
        $fieldData = explode('.', $field);

        return count($fieldData) > 1 ? $fieldData[0] : $field;
    }

    /**
     * Get field property part
     *
     * @param string $field
     * @param string $default
     */
    public static function getProperty(string $field, string $default = 'code'): string
    {
        $fieldData = explode('.', $field);

        return count($fieldData) > 1 ? $fieldData[1] : $default;
    }

    /**
     * Test if the field has a property
     *
     * @param string $field
     */
    public static function hasProperty(string $field): bool
    {
        return strpos($field, '.') !== false;
    }

    /**
     * Check if value is an array
     *
     * @param string $field
     * @param mixed  $value
     * @param string $className
     *
     * @throws InvalidPropertyTypeException
     */
    public static function checkArray(string $field, $value, string $className): void
    {
        if (!is_array($value)) {
            throw InvalidPropertyTypeException::arrayExpected(static::getCode($field), $className, $value);
        }
    }

    /**
     * Check if value is a datetime corresponding to a format
     *
     * @param string $field
     * @param string|\DateTimeInterface $value
     * @param string $format
     * @param string $dateMessageFormat
     * @param string $className
     *
     */
    public static function checkDateTime(string $field, $value, string $format, string $dateMessageFormat, string $className): void
    {
        if ($value instanceof \DateTimeInterface) {
            return;
        }

        if (!is_string($value)) {
            throw InvalidPropertyException::dateExpected($field, $format, $className, $value);
        }

        $dateTime = \DateTimeImmutable::createFromFormat($format, $value);

        if (false === $dateTime || 0 < $dateTime->getLastErrors()['warning_count']) {
            throw InvalidPropertyException::dateExpected(
                $field,
                $dateMessageFormat,
                $className,
                $value
            );
        }
    }

    /**
     * Check if value is a string
     *
     * @param string $field
     * @param mixed  $value
     * @param string $className
     *
     * @throws InvalidPropertyTypeException
     */
    public static function checkString(string $field, $value, string $className): void
    {
        if (!is_string($value)) {
            throw InvalidPropertyTypeException::stringExpected($field, $className, $value);
        }
    }

    /**
     * Check if value is a valid identifier
     *
     * @param string $field
     * @param mixed  $value
     * @param string $className
     *
     * @throws InvalidPropertyTypeException
     */
    public static function checkIdentifier(string $field, $value, string $className): void
    {
        $invalidIdField = static::hasProperty($field) && static::getProperty($field) === 'id' && !is_numeric($value);
        if ($invalidIdField) {
            throw InvalidPropertyTypeException::numericExpected(
                static::getCode($field),
                $className,
                $value
            );
        }

        $invalidDefaultField = !static::hasProperty($field) && !is_string($value) && !is_numeric($value);
        $invalidStringField = static::hasProperty($field) && static::getProperty($field) !== 'id' &&
            !is_string($value) && !is_numeric($value);

        if ($invalidDefaultField || $invalidStringField) {
            throw InvalidPropertyTypeException::stringExpected(static::getCode($field), $className, $value);
        }
    }
}
