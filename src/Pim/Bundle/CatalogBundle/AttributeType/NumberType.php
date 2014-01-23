<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\NumberType as FlexNumberType;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;

/**
 * Number attribute type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberType extends FlexNumberType
{
    /**
     * @staticvar integer
     */
    const DECIMAL_PLACES = 4;

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
                'fieldType' => 'switch',
                'options'   => array(
                    'attr' => $attribute->getId() ? array() : array('checked' => 'checked')
                )
            ),
            array(
                'name'      => 'negativeAllowed',
                'fieldType' => 'switch',
                'options'   => array(
                    'attr' => $attribute->getId() ? array() : array('checked' => 'checked')
                )
            ),
            array(
                'name'      => 'searchable',
                'fieldType' => 'switch'
            ),
            array(
                'name'      => 'localizable',
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
    protected function prepareValueFormOptions(FlexibleValueInterface $value)
    {
        $options = parent::prepareValueFormOptions($value);
        $options['precision'] = $value->getAttribute()->isDecimalsAllowed() ? self::DECIMAL_PLACES : 0;

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_catalog_number';
    }
}
