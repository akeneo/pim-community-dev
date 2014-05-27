<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Number attribute type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberType extends AbstractAttributeType
{
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
            ]
        ];

        $properties['unique']['options']['disabled']  = (bool) $attribute->getId();
        $properties['unique']['options']['read_only'] = (bool) $attribute->getId();

        return $properties;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareValueFormOptions(ProductValueInterface $value)
    {
        $options = parent::prepareValueFormOptions($value);
        $options['decimals_allowed'] = $value->getAttribute()->isDecimalsAllowed();

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
