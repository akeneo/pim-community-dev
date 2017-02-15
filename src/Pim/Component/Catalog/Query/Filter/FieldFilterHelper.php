<?php

namespace Pim\Component\Catalog\Query\Filter;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;

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

    /** @var string */
    const ID_PROPERTY = 'id';

    /**
     * Get field code part
     *
     * @param string $field
     *
     * @return string
     */
    public static function getCode($field)
    {
        $fieldData = explode('.', $field);

        return count($fieldData) > 1 ? $fieldData[0] : $field;
    }

    /**
     * Get field property part
     *
     * @param string $field
     * @param string $default
     *
     * @return string
     */
    public static function getProperty($field, $default = 'code')
    {
        $fieldData = explode('.', $field);

        return count($fieldData) > 1 ? $fieldData[1] : $default;
    }

    /**
     * Test if the field has a property
     *
     * @param string $field
     *
     * @return bool
     */
    public static function hasProperty($field)
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
    public static function checkArray($field, $value, $className)
    {
        if (!is_array($value)) {
            throw InvalidPropertyTypeException::arrayExpected(static::getCode($field), $className, $value);
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
    public static function checkIdentifier($field, $value, $className)
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
