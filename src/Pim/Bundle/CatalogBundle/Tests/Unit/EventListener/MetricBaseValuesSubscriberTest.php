<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\EventListener;

use Oro\Bundle\MeasureBundle\Manager\MeasureManager;
use Oro\Bundle\MeasureBundle\Convert\MeasureConverter;

use Pim\Bundle\CatalogBundle\Model\Metric;
use Pim\Bundle\CatalogBundle\EventListener\MetricBaseValuesSubscriber;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricBaseValuesSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MetricBaseValuesSubscriber
     */
    protected $metricSubscriber;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $config = $this->initializeMeasureConfig();

        $converter = new MeasureConverter(['measures_config' => $config]);
        $manager   = new MeasureManager();
        $manager->setMeasureConfig($config);

        $this->metricSubscriber = new MetricBaseValuesSubscriber($converter, $manager);
    }

    /**
     * Initialize config
     *
     * @return array
     */
    protected function initializeMeasureConfig()
    {
        return [
            'Length' => [
                'standard' => 'METER',
                'units'    => [
                    'KILOMETER' => [
                        'convert' => [['mul' => 1000]],
                        'symbol'  => 'km'
                    ],
                    'METER'     => [
                        'convert' => [['mul' => 1]],
                        'symbol'  => 'm'
                    ]
                ]
            ],
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
     * Test related method
     */
    public function testGetSubscribedEvent()
    {
        $this->assertEquals(
            ['prePersist', 'preUpdate'],
            $this->metricSubscriber->getSubscribedEvents()
        );
    }

    /**
     * Data provider for metrics
     *
     * @return array
     */
    public static function dataProviderForMetrics()
    {
        return [
            [
                [
                    'family'    => 'Weight',
                    'data'      => 1500,
                    'unit'      => 'GRAM',
                    'base_data' => 1.5,
                    'base_unit' => 'KILOGRAM'
                ]
            ],
            [
                [
                    'family'    => 'Length',
                    'data'      => 30,
                    'unit'      => 'KILOMETER',
                    'base_data' => 30000,
                    'base_unit' => 'METER'
                ]
            ]
        ];
    }

    /**
     * Test related method
     *
     * @param array $metricProperties
     *
     * @dataProvider dataProviderForMetrics
     */
    public function testPrePersist(array $metricProperties)
    {
        $metric = $this->createMetric($metricProperties);
        $eventArgs = $this->getEventMock('\Doctrine\ORM\Event\LifecycleEventArgs', $metric);

        $this->metricSubscriber->prePersist($eventArgs);

        $expectedMetric = $this->createMetric($metricProperties, true);
        $this->assertEquals($expectedMetric, $metric);
    }

    /**
     * Test related method
     *
     * @param array $metricProperties
     *
     * @dataProvider dataProviderForMetrics
     */
    public function testPostPersist(array $metricProperties)
    {
        $metric = $this->createMetric($metricProperties);
        $eventArgs = $this->getEventMock('\Doctrine\ORM\Event\PreUpdateEventArgs', $metric);

        $this->metricSubscriber->preUpdate($eventArgs);

        $expectedMetric = $this->createMetric($metricProperties, true);
        $this->assertEquals($expectedMetric, $metric);
    }

    /**
     * Create a metric object
     *
     * @param array   $properties
     * @param boolean $withBaseValues
     *
     * @return \Pim\Bundle\Catalog\Model\Metric
     */
    protected function createMetric(array $properties, $withBaseValues = false)
    {
        $metric = new Metric();
        $metric->setFamily($properties['family']);
        $metric->setData($properties['data']);
        $metric->setUnit($properties['unit']);

        if ($withBaseValues) {
            $metric->setBaseData($properties['base_data']);
            $metric->setBaseUnit($properties['base_unit']);
        }

        return $metric;
    }

    /**
     * Create an event mock
     *
     * @param string $class
     * @param object $object
     *
     * @return LifecycleEventArgs
     */
    protected function getEventMock($class, $object)
    {
        $eventArgs = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();

        $eventArgs
            ->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($object));

        return $eventArgs;
    }
}
