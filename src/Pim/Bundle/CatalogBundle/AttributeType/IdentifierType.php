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
        $properties = array(
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
                        'regexp' => 'Regular expression'
                    ),
                    'select2' => true
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
                'name'      => 'scopable',
                'fieldType' => 'pim_enrich_scopable',
                'options'   => array(
                    'data'      => false,
                    'disabled'  => true,
                    'read_only' => true
                )
            ),
            array(
                'name'      => 'unique',
                'fieldType' => 'switch',
                'options'   => array(
                    'data'      => true,
                    'disabled'  => true,
                    'read_only' => true
                )
            ),
            array(
                'name'      => 'required',
                'fieldType' => 'switch',
                'options'   => array(
                    'data'      => true,
                    'disabled'  => true,
                    'read_only' => true
                )
            ),
            array(
                'name'      => 'useableAsGridColumn',
                'fieldType' => 'switch',
                'options'   => array(
                    'data'      => true,
                    'disabled'  => true,
                    'read_only' => true
                )
            ),
            array(
                'name'      => 'useableAsGridFilter',
                'fieldType' => 'switch',
                'options'   => array(
                    'data'      => true,
                    'disabled'  => true,
                    'read_only' => true
                )
            ),
        );

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
