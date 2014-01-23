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

        $properties = [
            [
                'name'      => 'defaultValue',
                'fieldType' => $fieldType,
                'options'   => [
                    'widget' => 'single_text'
                ]
            ],
            [
                'name'      => 'dateType',
                'fieldType' => 'choice',
                'options'   => [
                    'required' => true,
                    'choices'  => [
                        'date'     => 'Date',
                        'time'     => 'Time',
                        'datetime' => 'Datetime'
                    ],
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId(),
                    'select2'   => true
                ],
            ],
            [
                'name'      => 'dateMin',
                'fieldType' => $fieldType,
                'options'   => [
                    'widget' => 'single_text'
                ]
            ],
            [
                'name'      => 'dateMax',
                'fieldType' => $fieldType,
                'options'   => [
                    'widget' => 'single_text'
                ]
            ],
            [
                'name'      => 'searchable',
                'fieldType' => 'switch'
            ],
            [
                'name'      => 'translatable',
                'fieldType' => 'switch',
                'options'   => [
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId()
                ]
            ],
            [
                'name'      => 'availableLocales',
                'fieldType' => 'pim_catalog_available_locales'
            ],
            [
                'name'      => 'scopable',
                'fieldType' => 'pim_catalog_scopable',
                'options'   => [
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId()
                ]
            ],
            [
                'name'      => 'unique',
                'fieldType' => 'switch',
                'options'   => [
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId()
                ]
            ]
        ];

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
