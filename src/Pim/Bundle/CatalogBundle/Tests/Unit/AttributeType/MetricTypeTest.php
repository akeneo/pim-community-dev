<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\AttributeType;

use Oro\Bundle\MeasureBundle\Manager\MeasureManager;
use Pim\Bundle\CatalogBundle\AttributeType\MetricType;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricTypeTest extends AttributeTypeTestCase
{
    protected $name = 'pim_catalog_metric';
    protected $backendType = 'metric';
    protected $formType = 'text';

    /**
     * {@inheritdoc}
     */
    protected function createAttributeType()
    {
        $measureManager = new MeasureManager();
        $measureManager->setMeasureConfig($this->initializeMetricConfig());

        return new MetricType($this->backendType, $this->formType, $this->guesser, $measureManager);
    }

    /**
     * Initialize config for measure manager
     *
     * @return array
     */
    protected function initializeMetricConfig()
    {
        return [
            'Weight' => [
                'standard' => 'KILOGRAM',
                'units'    => [
                    'GRAM'     => [
                        'convert' => [['mul' => 0.001]],
                        'symbol'  => 'g'
                    ],
                    'KILOGRAM' => [
                        'convert' => [['mul' => 1]],
                        'symbol'  => 'kg'
                    ]
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function testBuildValueFormType()
    {
        $factory = $this->getFormFactoryMock();
        $data = true;
        $value = $this->getFlexibleValueMock(
            [
                'data' => $data,
                'backendType' => $this->backendType,
                'attribute_options' => [
                    'metric_family' => 'Weight',
                    'default_metric_unit' => 'GRAM'
                ]
            ]
        );

        $factory
            ->expects($this->once())
            ->method('createNamed')
            ->with(
                $this->backendType,
                $this->formType,
                $data,
                [
                    'constraints' => ['constraints'],
                    'label' => null,
                    'required' => null,
                    'auto_initialize' => false,
                    'default_unit' => ['GRAM'],
                    'units' => ['GRAM' => 'g', 'KILOGRAM' => 'kg'],
                    'family' => 'Weight'
                ]
            );

        $this->target->buildValueFormType($factory, $value);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAttributeMock($backendType, $defaultValue, array $attributeOptions = [])
    {
        $attribute = parent::getAttributeMock($backendType, $defaultValue, $attributeOptions);

        if (isset($attributeOptions['metric_family'])) {
            $attribute
                ->expects($this->any())
                ->method('getMetricFamily')
                ->will($this->returnValue($attributeOptions['metric_family']));
        }

        if (isset($attributeOptions['default_metric_unit'])) {
            $attribute
                ->expects($this->any())
                ->method('getDefaultMetricUnit')
                ->will($this->returnValue($attributeOptions['default_metric_unit']));
        }

        return $attribute;
    }

    /**
     * Test related method
     */
    public function testBuildAttributeFormTypes()
    {
        $attFormType = $this->target->buildAttributeFormTypes(
            $this->getFormFactoryMock(),
            $this->getAttributeMock(null, null)
        );

        $this->assertCount(
            12,
            $attFormType
        );
    }
}
