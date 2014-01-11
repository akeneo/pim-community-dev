<?php

namespace Pim\Bundle\DataGridBundle\Extension\Formatter\Property;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\FieldProperty;

class FlexibleFieldProperty extends FieldProperty
{
    const BACKEND_TYPE_KEY = 'backend_type';

    /** @var array */
    public static $typeMatches = array(
        AbstractAttributeType::BACKEND_TYPE_DATE     => array(
            'field'         => FieldProperty::TYPE_DATE,
            'filter'        => 'flexible_date',
            'parent_filter' => 'date'
        ),
        AbstractAttributeType::BACKEND_TYPE_DATETIME => array(
            'field'         => FieldProperty::TYPE_DATETIME,
            'filter'        => 'flexible_datetime',
            'parent_filter' => 'datetime'
        ),
        AbstractAttributeType::BACKEND_TYPE_DECIMAL  => array(
            'field'         => FieldProperty::TYPE_DECIMAL,
            'filter'        => 'flexible_number',
            'parent_filter' => 'number'
        ),
        AbstractAttributeType::BACKEND_TYPE_BOOLEAN  => array(
            'field'         => FieldProperty::TYPE_BOOLEAN,
            'filter'        => 'flexible_boolean',
            'parent_filter' => 'number'
        ),
        AbstractAttributeType::BACKEND_TYPE_INTEGER  => array(
            'field'         => FieldProperty::TYPE_INTEGER,
            'filter'        => 'flexible_number',
            'parent_filter' => 'number'
        ),
        AbstractAttributeType::BACKEND_TYPE_OPTION   => array(
            'field'         => FieldProperty::TYPE_STRING,
            'filter'        => 'flexible_choice',
            'parent_filter' => 'choice',
            'field_options' => array('multiple' => true)
        ),
        AbstractAttributeType::BACKEND_TYPE_OPTIONS  => array(
            'field'         => FieldProperty::TYPE_STRING,
            'filter'        => 'flexible_choice',
            'parent_filter' => 'choice',
            'field_options' => array('multiple' => true)
        ),
        AbstractAttributeType::BACKEND_TYPE_TEXT     => array(
            'field'         => FieldProperty::TYPE_STRING,
            'filter'        => 'flexible_string',
            'parent_filter' => 'string'
        ),
        AbstractAttributeType::BACKEND_TYPE_VARCHAR  => array(
            'field'         => FieldProperty::TYPE_STRING,
            'filter'        => 'flexible_string',
            'parent_filter' => 'string'
        ),
        AbstractAttributeType::BACKEND_TYPE_PRICE    => array(
            'field'         => FieldProperty::TYPE_STRING,
            'filter'        => 'flexible_price',
            'parent_filter' => 'string'
        ),
        AbstractAttributeType::BACKEND_TYPE_METRIC   => array(
            'field'         => FieldProperty::TYPE_STRING,
            'filter'        => 'flexible_metric',
            'parent_filter' => 'metric'
       )
    );

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
