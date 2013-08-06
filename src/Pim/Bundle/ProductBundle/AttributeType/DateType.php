<?php

namespace Pim\Bundle\ProductBundle\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\DateType as OroDateType;

/**
 * Date attribute type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class DateType extends OroDateType
{
    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AbstractAttribute $attribute)
    {
        $fieldType = $attribute->getDateType() ?: 'datetime';

        $properties = array(
            array(
                'name'      => 'defaultValue',
                'fieldType' => $fieldType,
                'options'   => array(
                    'widget' => 'single_text'
                )
            ),
            array(
                'name'      => 'dateType',
                'fieldType' => 'choice',
                'options'   => array(
                    'required' => true,
                    'choices'  => array(
                        'date'     => 'Date',
                        'time'     => 'Time',
                        'datetime' => 'Datetime'
                    )
                )
            ),
            array(
                'name'      => 'dateMin',
                'fieldType' => $fieldType,
                'options'   => array(
                    'widget' => 'single_text'
                )
            ),
            array(
                'name'      => 'dateMax',
                'fieldType' => $fieldType,
                'options'   => array(
                    'widget' => 'single_text'
                )
            ),
            array(
                'name'      => 'searchable',
                'fieldType' => 'checkbox'
            ),
            array(
                'name'      => 'translatable',
                'fieldType' => 'checkbox',
                'options'   => array(
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId()
                )
            ),
            array(
                'name'      => 'availableLocales',
                'fieldType' => 'pim_product_available_locales'
            ),
            array(
                'name'      => 'scopable',
                'fieldType' => 'pim_product_scopable',
                'options'   => array(
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId()
                )
            ),
            array(
                'name'      => 'unique',
                'fieldType' => 'checkbox',
                'options'   => array(
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId()
                )
            )
        );

        return $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product_date';
    }
}
