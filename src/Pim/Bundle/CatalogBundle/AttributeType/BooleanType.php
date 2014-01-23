<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\BooleanType as FlexBooleanType;

/**
 * Boolean attribute type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanType extends FlexBooleanType
{
    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AbstractAttribute $attribute)
    {
        $properties = [
            [
                'name'      => 'defaultValue',
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
                    'disabled'  => true,
                    'read_only' => true
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
        return 'pim_catalog_boolean';
    }
}
