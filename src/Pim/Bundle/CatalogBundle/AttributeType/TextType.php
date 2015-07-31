<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

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
                'fieldType' => 'text'
            ],
            'validationRule' => [
                'name'      => 'validationRule',
                'fieldType' => 'choice',
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

        $properties['unique']['options']['disabled']  = (bool) $attribute->getId();
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
