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
        return [
            'maxCharacters' => [
                'name'      => 'maxCharacters',
                'fieldType' => 'integer'
            ],
            'validationRule' => [
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
            'validationRegexp' => [
                'name' => 'validationRegexp'
            ],
            'searchable' => [
                'name'      => 'searchable',
                'fieldType' => 'switch'
            ],
            'scopable' => [
                'name'      => 'scopable',
                'fieldType' => 'pim_enrich_scopable',
                'options'   => [
                    'data'      => false,
                    'disabled'  => true,
                    'read_only' => true
                ]
            ],
            'unique' => [
                'name'      => 'unique',
                'fieldType' => 'switch',
                'options'   => [
                    'data'      => true,
                    'disabled'  => true,
                    'read_only' => true
                ]
            ],
            'required' => [
                'name'      => 'required',
                'fieldType' => 'switch',
                'options'   => [
                    'data'      => true,
                    'disabled'  => true,
                    'read_only' => true
                ]
            ],
            'useableAsGridColumn' => [
                'name'      => 'useableAsGridColumn',
                'fieldType' => 'switch',
                'options'   => [
                    'data'      => true,
                    'disabled'  => true,
                    'read_only' => true
                ]
            ],
            'useableAsGridFilter' => [
                'name'      => 'useableAsGridFilter',
                'fieldType' => 'switch',
                'options'   => [
                    'data'      => true,
                    'disabled'  => true,
                    'read_only' => true
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_catalog_identifier';
    }
}
