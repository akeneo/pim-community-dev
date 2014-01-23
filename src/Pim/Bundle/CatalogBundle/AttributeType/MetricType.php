<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\MetricType as FlexMetricType;

/**
 * Metric attribute type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricType extends FlexMetricType
{
    /**
     * {@inheritdoc}
     */
    protected function prepareValueFormOptions(FlexibleValueInterface $value)
    {
        $options = parent::prepareValueFormOptions($value);
        $options['default_unit'] = [$options['default_unit']];

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AbstractAttribute $attribute)
    {
        $properties = [
            [
                'name' => 'defaultValue'
            ],
            [
                'name'      => 'numberMin',
                'fieldType' => 'number'
            ],
            [
                'name'      => 'numberMax',
                'fieldType' => 'number'
            ],
            [
                'name'      => 'decimalsAllowed',
                'fieldType' => 'switch',
                'options'   => [
                    'attr' => $attribute->getId() ? [] : ['checked' => 'checked']
                ]
            ],
            [
                'name'      => 'negativeAllowed',
                'fieldType' => 'switch',
                'options'   => [
                    'attr' => $attribute->getId() ? [] : ['checked' => 'checked']
                ]
            ],
            [
                'name'    => 'metricFamily',
                'options' => [
                    'required'  => true,
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId()
                ]
            ],
            [
                'name'    => 'defaultMetricUnit',
                'options' => [
                    'required' => true
                ]
            ],
            [
                'name'      => 'searchable',
                'fieldType' => 'switch'
            ],
            [
                'name'      => 'translatable',
                'fieldType' => 'switch',
                'options'   => [
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId()
                ]
            ],
            [
                'name'      => 'availableLocales',
                'fieldType' => 'pim_catalog_available_locales'
            ],
            [
                'name'      => 'scopable',
                'fieldType' => 'pim_catalog_scopable',
                'options'   => [
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId()
                ]
            ],
            [
                'name'      => 'unique',
                'fieldType' => 'switch',
                'options'   => [
                    'disabled'  => true,
                    'read_only' => true
                ]
            ]
        ];

        return $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_catalog_metric';
    }
}
