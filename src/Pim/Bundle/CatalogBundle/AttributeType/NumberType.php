<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\NumberType as FlexNumberType;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;

/**
 * Number attribute type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberType extends FlexNumberType
{
    /**
     * @staticvar integer
     */
    const DECIMAL_PLACES = 4;

    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AbstractAttribute $attribute)
    {
        $properties = [
            [
                'name'      => 'defaultValue',
                'fieldType' => 'number'
            ],
            [
                'name'      => 'numberMin',
                'fieldType' => 'number'
            ],
            [
                'name'      => 'numberMax',
                'fieldType' => 'number'
            ],
            [
                'name'      => 'decimalsAllowed',
                'fieldType' => 'switch',
                'options'   => [
                    'attr' => $attribute->getId() ? [] : ['checked' => 'checked']
                ]
            ],
            [
                'name'      => 'negativeAllowed',
                'fieldType' => 'switch',
                'options'   => [
                    'attr' => $attribute->getId() ? [] : ['checked' => 'checked']
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
    protected function prepareValueFormOptions(FlexibleValueInterface $value)
    {
        $options = parent::prepareValueFormOptions($value);
        $options['precision'] = $value->getAttribute()->isDecimalsAllowed() ? self::DECIMAL_PLACES : 0;

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_catalog_number';
    }
}
