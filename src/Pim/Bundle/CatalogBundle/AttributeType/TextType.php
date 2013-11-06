<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\TextType as FlexTextType;

/**
 * Text attribute type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextType extends FlexTextType
{
    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AbstractAttribute $attribute)
    {
        $properties = array(
            array(
                'name' => 'defaultValue'
            ),
            array(
                'name'      => 'maxCharacters',
                'fieldType' => 'integer'
            ),
            array(
                'name'      => 'validationRule',
                'fieldType' => 'choice',
                'options'   => array(
                    'choices' => array(
                        null     => 'None',
                        'email'  => 'E-mail',
                        'url'    => 'URL',
                        'regexp' => 'Regular expression'
                    )
                )
            ),
            array(
                'name' => 'validationRegexp'
            ),
            array(
                'name'      => 'searchable',
                'fieldType' => 'switch'
            ),
            array(
                'name'      => 'translatable',
                'fieldType' => 'switch',
                'options'   => array(
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId()
                )
            ),
            array(
                'name'      => 'availableLocales',
                'fieldType' => 'pim_catalog_available_locales'
            ),
            array(
                'name'      => 'scopable',
                'fieldType' => 'pim_catalog_scopable',
                'options'   => array(
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId()
                )
            ),
            array(
                'name'      => 'unique',
                'fieldType' => 'switch',
                'options'   => array(
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId()
                )
            )
        );

        return $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_catalog_text';
    }
}
