<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Query;

class FieldFilterHelper
{
    const CODE_PROPERTY = 'code';
    const ID_PROPERTY   = 'id';

    public static function getCode($field)
    {
        $fieldData = explode('.', $field);

        return count($fieldData) > 1 ? $fieldData[0] : $field;
    }

    public static function getProperty($field, $default = 'id')
    {
        $fieldData = explode('.', $field);

        return count($fieldData) > 1 ? $fieldData[1] : $default;
    }

    public static function getWithProperty($field, $default = 'id')
    {
        return strpos($field, '.') !== false ? $field : sprintf('%s.%s', $field, $default);
    }

    public static function hasProperty($field)
    {
        return strpos($field, '.') !== false;
    }
}
