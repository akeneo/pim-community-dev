<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;

/**
 * Metric attribute type
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AttributeInterface $attribute)
    {
        return parent::defineCustomAttributeProperties($attribute) + [
            'numberMin' => [
                'name'      => 'numberMin',
                'fieldType' => 'pim_number'
            ],
            'numberMax' => [
                'name'      => 'numberMax',
                'fieldType' => 'pim_number'
            ],
            'decimalsAllowed' => [
                'name'      => 'decimalsAllowed',
                'fieldType' => 'switch',
                'options'   => [
                    'attr' => $attribute->getId() ? [] : ['checked' => 'checked']
                ]
            ],
            'negativeAllowed' => [
                'name'      => 'negativeAllowed',
                'fieldType' => 'switch',
                'options'   => [
                    'attr' => $attribute->getId() ? [] : ['checked' => 'checked']
                ]
            ],
            'metricFamily' => [
                'name'    => 'metricFamily',
                'options' => [
                    'required'  => true,
                    'disabled'  => (bool) $attribute->getId(),
                    'read_only' => (bool) $attribute->getId()
                ]
            ],
            'defaultMetricUnit' => [
                'name'    => 'defaultMetricUnit',
                'options' => [
                    'required' => true
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return AttributeTypes::METRIC;
    }
}
