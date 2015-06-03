<?php

namespace Pim\Bundle\CatalogBundle\Query\Filter;

use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;

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
    const ID_PROPERTY   = 'id';

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
    public static function getProperty($field, $default = 'id')
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
     * @param string $filter
     */
    public static function checkArray($field, $value, $filter)
    {
        if (!is_array($value)) {
            throw InvalidArgumentException::arrayExpected(static::getCode($field), 'filter', $filter, gettype($value));
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
        $invalidIdField = static::hasProperty($field) && static::getProperty($field) === 'id' && !is_numeric($value);
        $invalidDefaultField = !static::hasProperty($field) && !is_numeric($value);

        if ($invalidIdField || $invalidDefaultField) {
            throw InvalidArgumentException::numericExpected(
                static::getCode($field),
                'filter',
                $filter,
                gettype($value)
            );
        }

        $invalidStringField = static::hasProperty($field) && static::getProperty($field) !== 'id' && !is_string($value);
        if ($invalidStringField) {
            throw InvalidArgumentException::stringExpected(static::getCode($field), 'filter', $filter, gettype($value));
        }
    }
}
