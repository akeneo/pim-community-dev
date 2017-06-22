<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Bundle\UIBundle\Form\Type\NumberType as FormNumberType;
use Pim\Bundle\UIBundle\Form\Type\SwitchType;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;

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
    protected function defineCustomAttributeProperties(AttributeInterface $attribute)
    {
        $properties = parent::defineCustomAttributeProperties($attribute) + [
            'numberMin' => [
                'name'      => 'numberMin',
                'fieldType' => FormNumberType::class
            ],
            'numberMax' => [
                'name'      => 'numberMax',
                'fieldType' => FormNumberType::class
            ],
            'decimalsAllowed' => [
                'name'      => 'decimalsAllowed',
                'fieldType' => SwitchType::class,
                'options'   => [
                    'attr' => $attribute->getId() ? [] : ['checked' => 'checked']
                ]
            ],
            'negativeAllowed' => [
                'name'      => 'negativeAllowed',
                'fieldType' => SwitchType::class,
                'options'   => [
                    'attr' => $attribute->getId() ? [] : ['checked' => 'checked']
                ]
            ]
        ];

        $properties['unique']['options']['disabled'] = (bool) $attribute->getId();
        $properties['unique']['options']['read_only'] = (bool) $attribute->getId();

        return $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return AttributeTypes::NUMBER;
    }
}
