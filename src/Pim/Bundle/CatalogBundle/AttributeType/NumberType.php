<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\NumberType as OroNumberType;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Number attribute type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberType extends OroNumberType
{
    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AbstractAttribute $attribute)
    {
        $properties = array(
            array(
                'name'      => 'defaultValue',
                'fieldType' => 'number'
            ),
            array(
                'name'      => 'numberMin',
                'fieldType' => 'number'
            ),
            array(
                'name'      => 'numberMax',
                'fieldType' => 'number'
            ),
            array(
                'name'      => 'decimalsAllowed',
                'fieldType' => 'checkbox',
                'options'   => array(
                    'attr' => $attribute->getId() ? array() : array('checked' => 'checked')
                )
            ),
            array(
                'name'      => 'negativeAllowed',
                'fieldType' => 'checkbox',
                'options'   => array(
                    'attr' => $attribute->getId() ? array() : array('checked' => 'checked')
                )
            ),
            array(
                'name'      => 'searchable',
                'fieldType' => 'checkbox'
            ),
            array(
                'name'      => 'translatable',
                'fieldType' => 'checkbox',
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
                'fieldType' => 'checkbox',
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
        return 'pim_catalog_number';
    }
}
