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
        $properties = parent::defineCustomAttributeProperties($attribute) + [
            'defaultValue' => [
                'name'      => 'defaultValue',
                'fieldType' => 'oro_date',
                'options'   => [
                    'widget' => 'single_text'
                ]
            ],
            'dateMin' => [
                'name'      => 'dateMin',
                'fieldType' => 'oro_date',
                'options'   => [
                    'widget' => 'single_text'
                ]
            ],
            'dateMax' => [
                'name'      => 'dateMax',
                'fieldType' => 'oro_date',
                'options'   => [
                    'widget' => 'single_text'
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
    public function getName()
    {
        return 'pim_catalog_date';
    }
}
