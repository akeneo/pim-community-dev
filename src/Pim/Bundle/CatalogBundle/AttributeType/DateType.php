<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Bundle\UIBundle\Form\Type\DateType as FormDateType;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Date attribute type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AttributeInterface $attribute)
    {
        $properties = parent::defineCustomAttributeProperties($attribute) + [
            'dateMin' => [
                'name'      => 'dateMin',
                'fieldType' => FormDateType::class,
                'options'   => [
                    'widget' => 'single_text'
                ]
            ],
            'dateMax' => [
                'name'      => 'dateMax',
                'fieldType' => FormDateType::class,
                'options'   => [
                    'widget' => 'single_text'
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
        return AttributeTypes::DATE;
    }
}
