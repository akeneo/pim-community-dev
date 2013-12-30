<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer\Property;

use Pim\Bundle\ImportExportBundle\Transformer\Property\MetricTransformer;
use Pim\Bundle\CatalogBundle\Model\Metric;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function getSetValueData()
    {
        return array(
            'only_data'     => array(null, '54.25', false, '54.25', null),
            'create'        => array(null, '54.25', true, '54.25', null),
            'data_and_unit' => array(null, '54.25 KG', false, '54.25', 'KG'),
            'only_unit'     => array('unit', 'KG', false, null, 'KG'),
            'null'          => array(null, null, false, null, null)
        );
    }

    /**
     * @dataProvider getSetValueData
     */
    public function testSetValue($columnSuffix, $data, $createMetric, $expectedData, $expectedUnit)
    {
        $columnInfo = $this->getMock('Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoInterface');
        $columnInfo->expects($this->any())
            ->method('getSuffixes')
            ->will($this->returnValue(array($columnSuffix)));

        $attribute = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Model\ProductAttributeInterface')
            ->setMethods(array('getMetricFamily'))
            ->getMock();
        $columnInfo->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));
        $attribute->expects($this->any())
            ->method('getMetricFamily')
            ->will($this->returnValue('metric_family'));

        $object = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Model\ProductValueInterface')
            ->setMethods(array('getMetric', 'setMetric', '__toString'))
            ->getMock();

        $metric = null;
        if ($createMetric) {
            $object->expects($this->once())
                ->method('setMetric')
                ->will(
                    $this->returnCallback(
                        function ($createdMetric) use (&$metric) {
                            $metric = $createdMetric;
                        }
                    )
                );
        } else {
            $metric = new Metric;
            $metric->setFamily('metric_family');
        }
        $object->expects($this->any())
            ->method('getMetric')
            ->will(
                $this->returnCallback(
                    function () use (&$metric) {
                        return $metric;
                    }
                )
            );

        $transformer = new MetricTransformer();
        $transformer->setValue($object, $columnInfo, $data);
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Model\Metric', $metric);
        $this->assertEquals('metric_family', $metric->getFamily());
        $this->assertEquals($expectedData, $metric->getData());
        $this->assertEquals($expectedUnit, $metric->getUnit());
    }
}
