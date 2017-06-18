<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Bundle\UIBundle\Form\Type\NumberType as FormNumberType;
use Pim\Bundle\UIBundle\Form\Type\SwitchType;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Price attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceCollectionType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AttributeInterface $attribute)
    {
        return parent::defineCustomAttributeProperties($attribute) + [
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
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return AttributeTypes::PRICE_COLLECTION;
    }
}
