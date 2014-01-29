<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\DateType as FlexDateType;

/**
 * Date attribute type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateType extends FlexDateType
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
                    ),
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId(),
                    'select2'   => true
                ),
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
                'fieldType' => 'switch'
            ),
            array(
                'name'      => 'localizable',
                'fieldType' => 'switch',
                'options'   => array(
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId()
                )
            ),
            array(
                'name'      => 'availableLocales',
                'fieldType' => 'pim_enrich_available_locales'
            ),
            array(
                'name'      => 'scopable',
                'fieldType' => 'pim_enrich_scopable',
                'options'   => array(
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId()
                )
            ),
            array(
                'name'      => 'unique',
                'fieldType' => 'switch',
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
        return 'pim_catalog_date';
    }
}
