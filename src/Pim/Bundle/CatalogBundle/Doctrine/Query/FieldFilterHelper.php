<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Query;

use Pim\Bundle\CatalogBundle\Doctrine\InvalidArgumentException;

class FieldFilterHelper
{
    /** @var string */
    const CODE_PROPERTY = 'code';

    /** @var string */
    const ID_PROPERTY   = 'id';

    /**
     * Get field code part
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
     * @param string $field
     * @param string $default
     *
     * @return string
     */
    public static function getProperty($field, $default = 'id')
    {
        $fieldData = explode('.', $field);

        return count($fieldData) > 1 ? $fieldData[1] : $default;
    }

    /**
     * Test if the field has a property
     * @param string $field
     *
     * @return boolean
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
     * @param string $filter
     */
    public static function checkArray($field, $value, $filter)
    {
        if (!is_array($value)) {
            throw InvalidArgumentException::arrayExpected(static::getCode($field), 'filter', $filter);
        }
    }

    /**
     * Check if value is a valid identifier
     *
     * @param string $field
     * @param mixed  $value
     * @param string $filter
     */
    public static function checkIdentifier($field, $value, $filter)
    {
        if ((
                static::hasProperty($field) &&
                static::getProperty($field) === 'id' &&
                !is_numeric($value)
            ) ||
            (
                !static::hasProperty($field) &&
                !is_numeric($value)
            )
        ) {
            throw InvalidArgumentException::numericExpected(static::getCode($field), 'filter', $filter);
        }

        if (static::hasProperty($field) &&
            static::getProperty($field) !== 'id' &&
            !is_string($value)
        ) {
            throw InvalidArgumentException::stringExpected(static::getCode($field), 'filter', $filter);
        }
    }
}
