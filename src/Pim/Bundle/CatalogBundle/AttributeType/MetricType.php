<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Akeneo\Bundle\MeasureBundle\Manager\MeasureManager;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\Metric;
use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesserInterface;

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
     * @var MeasureManager $manager
     */
    protected $manager;

    /**
     * Constructor
     *
     * @param string                     $backendType       the backend type
     * @param string                     $formType          the form type
     * @param ConstraintGuesserInterface $constraintGuesser the form type
     * @param MeasureManager             $manager           The measure manager
     */
    public function __construct(
        $backendType,
        $formType,
        ConstraintGuesserInterface $constraintGuesser,
        MeasureManager $manager
    ) {
        parent::__construct($backendType, $formType, $constraintGuesser);

        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareValueFormOptions(ProductValueInterface $value)
    {
        $options = array_merge(
            parent::prepareValueFormOptions($value),
            array(
                'units'        => $this->manager->getUnitSymbolsForFamily(
                    $value->getAttribute()->getMetricFamily()
                ),
                'default_unit' => $value->getAttribute()->getDefaultMetricUnit(),
                'family'       => $value->getAttribute()->getMetricFamily()
            )
        );
        $options['default_unit'] = array($options['default_unit']);

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareValueFormData(ProductValueInterface $value)
    {
        if (!is_null($value->getData())) {
            return $value->getData();
        };

        $data = new Metric();
        $data->setData($value->getAttribute()->getDefaultValue());

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function defineCustomAttributeProperties(AbstractAttribute $attribute)
    {
        return parent::defineCustomAttributeProperties($attribute) + [
            'defaultValue' => [
                'name' => 'defaultValue'
            ],
            'numberMin' => [
                'name'      => 'numberMin',
                'fieldType' => 'number'
            ],
            'numberMax' => [
                'name'      => 'numberMax',
                'fieldType' => 'number'
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
        return 'pim_catalog_metric';
    }
}
