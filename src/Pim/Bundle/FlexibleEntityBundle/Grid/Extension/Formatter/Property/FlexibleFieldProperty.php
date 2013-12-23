<?php

namespace Pim\Bundle\FlexibleEntityBundle\Grid\Extension\Formatter\Property;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\FieldProperty;

class FlexibleFieldProperty extends FieldProperty
{
    const BACKEND_TYPE_KEY = 'backend_type';

    /** @var array */
    public static $typeMatches = [
        AbstractAttributeType::BACKEND_TYPE_DATE     => [
            'field'         => FieldProperty::TYPE_DATE,
            'filter'        => 'flexible_date',
            'parent_filter' => 'date'
        ],
        AbstractAttributeType::BACKEND_TYPE_DATETIME => [
            'field'         => FieldProperty::TYPE_DATETIME,
            'filter'        => 'flexible_datetime',
            'parent_filter' => 'datetime'
        ],
        AbstractAttributeType::BACKEND_TYPE_DECIMAL  => [
            'field'         => FieldProperty::TYPE_DECIMAL,
            'filter'        => 'flexible_number',
            'parent_filter' => 'number'
        ],
        AbstractAttributeType::BACKEND_TYPE_BOOLEAN  => [
            'field'         => FieldProperty::TYPE_BOOLEAN,
            'filter'        => 'flexible_boolean',
            'parent_filter' => 'number'
        ],
        AbstractAttributeType::BACKEND_TYPE_INTEGER  => [
            'field'         => FieldProperty::TYPE_INTEGER,
            'filter'        => 'flexible_number',
            'parent_filter' => 'number'
        ],
        AbstractAttributeType::BACKEND_TYPE_OPTION   => [
            'field'         => FieldProperty::TYPE_SELECT,
            'filter'        => 'flexible_choice',
            'parent_filter' => 'choice'
        ],
        AbstractAttributeType::BACKEND_TYPE_TEXT     => [
            'field'         => FieldProperty::TYPE_STRING,
            'filter'        => 'flexible_string',
            'parent_filter' => 'string'
        ],
        AbstractAttributeType::BACKEND_TYPE_VARCHAR  => [
            'field'         => FieldProperty::TYPE_STRING,
            'filter'        => 'flexible_string',
            'parent_filter' => 'string'
        ],
        AbstractAttributeType::BACKEND_TYPE_PRICE    => [
            'field'         => FieldProperty::TYPE_STRING,
            'filter'        => 'flexible_string',
            'parent_filter' => 'string'
        ],
        AbstractAttributeType::BACKEND_TYPE_METRIC   => [
            'field'         => FieldProperty::TYPE_STRING,
            'filter'        => 'flexible_string',
            'parent_filter' => 'string'
        ]
    ];

    /** @var array */
    protected $excludeParams = [self::BACKEND_TYPE_KEY];

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->params[self::FRONTEND_TYPE_KEY] = isset(self::$typeMatches[$this->get(self::BACKEND_TYPE_KEY)])
            ? self::$typeMatches[$this->get(self::BACKEND_TYPE_KEY)]['field'] : FieldProperty::TYPE_STRING;
    }

    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        if (is_object($value) && is_callable([$value, '__toString'])) {
            $value = $value->__toString();
        } elseif (false === $value) {
            return null;
        }

        return parent::convertValue($value);
    }
}
