<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * Identifier attribute type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AttributeInterface $attribute)
    {
        return [
            'maxCharacters' => [
                'name'      => 'maxCharacters',
                'fieldType' => 'text'
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
        return AttributeTypes::IDENTIFIER;
    }
}
