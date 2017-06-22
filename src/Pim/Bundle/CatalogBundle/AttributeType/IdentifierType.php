<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Bundle\UIBundle\Form\Type\SwitchType;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType as FormTextType;

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
                'fieldType' => FormTextType::class
            ],
            'validationRule' => [
                'name'      => 'validationRule',
                'fieldType' => ChoiceType::class,
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
                'fieldType' => SwitchType::class,
                'options'   => [
                    'data'      => false,
                    'disabled'  => true,
                    'read_only' => true
                ]
            ],
            'unique' => [
                'name'      => 'unique',
                'fieldType' => SwitchType::class,
                'options'   => [
                    'data'      => true,
                    'disabled'  => true,
                    'read_only' => true
                ]
            ],
            'required' => [
                'name'      => 'required',
                'fieldType' => SwitchType::class,
                'options'   => [
                    'data'      => true,
                    'disabled'  => true,
                    'read_only' => true
                ]
            ],
            'useableAsGridFilter' => [
                'name'      => 'useableAsGridFilter',
                'fieldType' => SwitchType::class,
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

    /**
     * {@inheritdoc}
     */
    public function isUnique()
    {
        return true;
    }
}
