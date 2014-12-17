<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Query;

class FieldHelper
{
    public static function getCode($field, $default = null)
    {
        $fieldData = explode('.', $field);

        return count($fieldData) > 1 ? $fieldData[0] : $default;
    }

    public static function getIdentifier($field, $default = 'id')
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
