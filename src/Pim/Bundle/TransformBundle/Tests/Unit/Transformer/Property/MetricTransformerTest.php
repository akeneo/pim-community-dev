<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Transformer\Property;

use Pim\Bundle\CatalogBundle\Factory\MetricFactory;
use Pim\Bundle\TransformBundle\Transformer\Property\MetricTransformer;
use Pim\Component\Catalog\Model\Metric;

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
        return [
            'only_data'     => [null, '54.25', false, '54.25', null],
            'create'        => [null, '54.25', true, '54.25', null],
            'data_and_unit' => [null, '54.25 KG', false, '54.25', 'KG'],
            'only_unit'     => ['unit', 'KG', false, null, 'KG'],
            'null'          => [null, null, false, null, null]
        ];
    }

    /**
     * @dataProvider getSetValueData
     */
    public function testSetValue($columnSuffix, $data, $createMetric, $expectedData, $expectedUnit)
    {
        $columnInfo = $this->getMock('Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface');
        $columnInfo->expects($this->any())
            ->method('getSuffixes')
            ->will($this->returnValue([$columnSuffix]));

        $attribute = $this->getMockBuilder('Pim\Component\Catalog\Model\AbstractAttribute')
            ->setMethods(['getMetricFamily'])
            ->getMock();
        $columnInfo->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));
        $attribute->expects($this->any())
            ->method('getMetricFamily')
            ->will($this->returnValue('metric_family'));

        $object = $this->getMockBuilder('Pim\Component\Catalog\Model\ProductValueInterface')
            ->setMethods(
                [
                    'setText',
                    'setDatetime',
                    'setInteger',
                    'setId',
                    'getOption',
                    'getMedia',
                    'getDecimal',
                    'setDecimal',
                    'setAttribute',
                    'addOption',
                    'getBoolean',
                    'setOptions',
                    'setPrices',
                    'getId',
                    'setVarchar',
                    'setBoolean',
                    'getData',
                    'getMetric',
                    'getDate',
                    'getAttribute',
                    'getEntity',
                    'setMedia',
                    'getPrices',
                    'getOptions',
                    'getLocale',
                    'setMetric',
                    'addPrice',
                    'getVarchar',
                    'removePrice',
                    'hasData',
                    'setScope',
                    'removeOption',
                    'getText',
                    'setData',
                    'setOption',
                    'getPrice',
                    'setDate',
                    'addData',
                    'setLocale',
                    'isRemovable',
                    'getScope',
                    'getDatetime',
                    'setEntity',
                    'getInteger',
                    'getProduct',
                    'setProduct',
                    '__toString'
                ]
            )
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
            $metric = new Metric();
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

        $metricFactory = new MetricFactory('Pim\Component\Catalog\Model\Metric');
        $transformer = new MetricTransformer($metricFactory);
        $transformer->setValue($object, $columnInfo, $data);
        $this->assertInstanceOf('Pim\Component\Catalog\Model\Metric', $metric);
        $this->assertEquals('metric_family', $metric->getFamily());
        $this->assertEquals($expectedData, $metric->getData());
        $this->assertEquals($expectedUnit, $metric->getUnit());
    }
}
