<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit;

use Symfony\Component\Yaml\Yaml;

use Oro\Bundle\MeasureBundle\Manager\MeasureManager;
use Oro\Bundle\MeasureBundle\Convert\MeasureConverter;

use Pim\Bundle\FlexibleEntityBundle\Entity\Metric;
use Pim\Bundle\FlexibleEntityBundle\EventListener\MetricBaseValuesSubscriber;

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
        $config = $this->initializeConfig();

        $converter = new MeasureConverter($config);
        $manager   = new MeasureManager();
        $manager->setMeasureConfig($config['measures_config']);

        $this->metricSubscriber = new MetricBaseValuesSubscriber($converter, $manager);
    }

    /**
     * Initialize a configuration for metrics
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function initializeConfig()
    {
        $configFile = realpath(dirname(__FILE__) .'/../../Resources/config/measure.yml');
        if (!file_exists($configFile)) {
            throw new \Exception('Config file not exists');
        }

        return Yaml::parse($configFile);
    }

    /**
     * Test related method
     */
    public function testGetSubscribedEvent()
    {
        $this->assertEquals(
            array('prePersist', 'preUpdate'),
            $this->metricSubscriber->getSubscribedEvents()
        );
    }

    /**
     * Data provider for metrics
     *
     * @return array
     *
     * @static
     */
    public static function dataProviderForMetrics()
    {
        return array(
            array(
                array(
                    'family'    => 'Weight',
                    'data'      => 1500,
                    'unit'      => 'GRAM',
                    'base_data' => 1.5,
                    'base_unit' => 'KILOGRAM'
                )
            ),
            array(
                array(
                    'family'    => 'Length',
                    'data'      => 30,
                    'unit'      => 'KILOMETER',
                    'base_data' => 30000,
                    'base_unit' => 'METER'
                )
            )
        );
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
     * @return \Pim\Bundle\FlexibleEntityBundle\Entity\Metric
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
