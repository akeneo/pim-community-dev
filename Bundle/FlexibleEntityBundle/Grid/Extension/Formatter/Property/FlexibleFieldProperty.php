<?php

namespace Oro\Bundle\FlexibleEntityBundle\Grid\Extension\Formatter\Property;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\FieldProperty;

class FlexibleFieldProperty extends FieldProperty
{
    /** @var array */
    public static $typeMatches = [
        AbstractAttributeType::BACKEND_TYPE_DATE     => [
            'field'  => FieldProperty::TYPE_DATE,
            'filter' => 'flexible_date'
        ],
        AbstractAttributeType::BACKEND_TYPE_DATETIME => [
            'field'  => FieldProperty::TYPE_DATETIME,
            'filter' => 'flexible_datetime'
        ],
        AbstractAttributeType::BACKEND_TYPE_DECIMAL  => [
            'field'  => FieldProperty::TYPE_DECIMAL,
            'filter' => 'flexible_number'
        ],
        AbstractAttributeType::BACKEND_TYPE_BOOLEAN  => [
            'field'  => FieldProperty::TYPE_BOOLEAN,
            'filter' => 'flexible_boolean'
        ],
        AbstractAttributeType::BACKEND_TYPE_INTEGER  => [
            'field'  => FieldProperty::TYPE_INTEGER,
            'filter' => 'flexible_number'
        ],
        AbstractAttributeType::BACKEND_TYPE_OPTION   => [
            'field'  => FieldProperty::TYPE_OPTIONS,
            'filter' => 'flexible_choice'
        ],
        AbstractAttributeType::BACKEND_TYPE_TEXT     => [
            'field'  => FieldProperty::TYPE_TEXT,
            'filter' => 'flexible_string'
        ],
        AbstractAttributeType::BACKEND_TYPE_VARCHAR  => [
            'field'  => FieldProperty::TYPE_TEXT,
            'filter' => 'flexible_string'
        ],
        AbstractAttributeType::BACKEND_TYPE_PRICE    => [
            'field'  => FieldProperty::TYPE_TEXT,
            'filter' => 'flexible_string'
        ],
        AbstractAttributeType::BACKEND_TYPE_METRIC   => [
            'field'  => FieldProperty::TYPE_TEXT,
            'filter' => 'flexible_string'
        ]
    ];

    public function init(array $params)
    {
        parent::init($params);

        $this->params['frontend_type'] = isset(self::$typeMatches[$this->get('backend_type')])
            ? self::$typeMatches[$this->get('backend_type')]['field'] : FieldProperty::TYPE_TEXT;
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
