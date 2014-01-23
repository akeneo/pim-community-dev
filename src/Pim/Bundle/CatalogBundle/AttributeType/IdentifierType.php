<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\TextType as FlexTextType;

/**
 * Identifier attribute type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierType extends FlexTextType
{
    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AbstractAttribute $attribute)
    {
        $properties = [
            [
                'name'      => 'maxCharacters',
                'fieldType' => 'integer'
            ],
            [
                'name'      => 'validationRule',
                'fieldType' => 'choice',
                'options'   => [
                    'choices' => [
                        null     => 'None',
                        'regexp' => 'Regular expression'
                    ],
                    'select2' => true
                ]
            ],
            [
                'name' => 'validationRegexp'
            ],
            [
                'name'      => 'searchable',
                'fieldType' => 'switch'
            ],
            [
                'name'      => 'scopable',
                'fieldType' => 'pim_catalog_scopable',
                'options'   => [
                    'data'      => false,
                    'disabled'  => true,
                    'read_only' => true
                ]
            ],
            [
                'name'      => 'unique',
                'fieldType' => 'switch',
                'options'   => [
                    'data'      => true,
                    'disabled'  => true,
                    'read_only' => true
                ]
            ],
            [
                'name'      => 'required',
                'fieldType' => 'switch',
                'options'   => [
                    'data'      => true,
                    'disabled'  => true,
                    'read_only' => true
                ]
            ],
            [
                'name'      => 'useableAsGridColumn',
                'fieldType' => 'switch',
                'options'   => [
                    'data'      => true,
                    'disabled'  => true,
                    'read_only' => true
                ]
            ],
            [
                'name'      => 'useableAsGridFilter',
                'fieldType' => 'switch',
                'options'   => [
                    'data'      => true,
                    'disabled'  => true,
                    'read_only' => true
                ]
            ],
        ];

        return $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_catalog_identifier';
    }
}
