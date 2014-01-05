<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Converter;

use Pim\Bundle\ImportExportBundle\Converter\MetricConverter;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricConverterTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->measureConverter = $this->getMeasureConverterMock();
        $this->converter = new MetricConverter($this->measureConverter);
    }

    public function testConvert()
    {
        $shoe = $this->getProductMock(
            array(
                $this->getProductValueMock(
                    $this->getAttributeMock('weight'),
                    $metric = $this->getMetricMock('Weight', 'KILOGRAM', 1)
                ),
                $this->getProductValueMock(
                    $this->getAttributeMock('surface'),
                    $this->getMetricMock('Surface', 'METER_SQUARE', 10)
                ),
                $this->getProductValueMock(
                    $this->getAttributeMock('bar'),
                    'foo'
                ),
            )
        );
        $products = array(
            $shoe
        );

        $channel = $this->getChannelMock(array('weight' => 'GRAM'));

        $this->measureConverter->expects($this->once())->method('setFamily');
        $this->measureConverter
            ->expects($this->once())
            ->method('convert')
            ->with('KILOGRAM', 'GRAM', 1)
            ->will($this->returnValue(0.001));

        $metric->expects($this->once())->method('setData')->with(0.001);
        $metric->expects($this->once())->method('setUnit')->with('GRAM');

        $this->converter->convert($products, $channel);
    }

    protected function getMeasureConverterMock()
    {
        return $this
            ->getMockBuilder('Oro\Bundle\MeasureBundle\Convert\MeasureConverter')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getProductMock($values)
    {
        $product = $this->getMock('Pim\Bundle\CatalogBundle\Model\Product');

        $product->expects($this->any())
            ->method('getValues')
            ->will($this->returnValue($values));

        return $product;
    }

    private function getProductValueMock($attribute, $data)
    {
        $value = $this->getMock('Pim\Bundle\CatalogBundle\Model\ProductValue');

        $value->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));

        $value->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($data));

        return $value;
    }

    protected function getMetricMock($family, $unit, $data)
    {
        $metric = $this->getMock('Pim\Bundle\CatalogBundle\Model\Metric');

        $metric->expects($this->any())->method('getFamily')->will($this->returnValue($family));
        $metric->expects($this->any())->method('getUnit')->will($this->returnValue($unit));
        $metric->expects($this->any())->method('getData')->will($this->returnValue($data));

        return $metric;
    }

    protected function getChannelMock(array $conversionUnits)
    {
        $channel = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Channel');

        $channel->expects($this->any())
            ->method('getConversionUnits')
            ->will($this->returnValue($conversionUnits));

        return $channel;
    }

    protected function getAttributeMock($code)
    {
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Attribute');

        $attribute->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        return $attribute;
    }
}
