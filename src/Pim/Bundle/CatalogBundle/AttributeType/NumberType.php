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
        $properties = parent::defineCustomAttributeProperties($attribute) + [
            'defaultValue' => [
                'name'      => 'defaultValue',
                'fieldType' => 'number'
            ],
            'numberMin' => [
                'name'      => 'numberMin',
                'fieldType' => 'number'
            ],
            'numberMax' => [
                'name'      => 'numberMax',
                'fieldType' => 'number'
            ],
            'decimalsAllowed' => [
                'name'      => 'decimalsAllowed',
                'fieldType' => 'switch',
                'options'   => [
                    'attr' => $attribute->getId() ? [] : ['checked' => 'checked']
                ]
            ],
            'negativeAllowed' => [
                'name'      => 'negativeAllowed',
                'fieldType' => 'switch',
                'options'   => [
                    'attr' => $attribute->getId() ? [] : ['checked' => 'checked']
                ]
            ],
            'searchable' => [
                'name'      => 'searchable',
                'fieldType' => 'switch'
            ]
        ];

        $properties['unique']['options']['disabled']  = (bool) $attribute->getId();
        $properties['unique']['options']['read_only'] = (bool) $attribute->getId();

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
