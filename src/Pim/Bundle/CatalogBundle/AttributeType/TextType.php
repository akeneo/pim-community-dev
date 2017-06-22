<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType as FormTextType;

/**
 * Text attribute type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AttributeInterface $attribute)
    {
        $properties = parent::defineCustomAttributeProperties($attribute) + [
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
                        'email'  => 'E-mail',
                        'url'    => 'URL',
                        'regexp' => 'Regular expression'
                    ],
                    'select2' => true
                ]
            ],
            'validationRegexp' => [
                'name' => 'validationRegexp'
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
        return AttributeTypes::TEXT;
    }
}
