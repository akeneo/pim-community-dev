<?php

namespace Pim\Bundle\TransformBundle\Tests\Unit\Transformer\Property;

use Pim\Bundle\TransformBundle\Transformer\Property\PricesTransformer;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PricesTransformerTest extends \PHPUnit_Framework_TestCase
{
    protected $builder;
    protected $transformer;

    protected function setUp()
    {
        $this->builder = $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Builder\ProductBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->transformer = new PricesTransformer($this->builder);
    }

    public function getSetValuesData()
    {
        return array(
            'single_price' => array('currency', '25', array('currency' => 25)),
            'array'        => array(null, array('cur1' => '14', 'cur2' => '25'), array('cur1' => '14', 'cur2' => '25')),
            'string'       => array(null, '10 cur1, 12.2 cur2', array('cur1' => '10', 'cur2' => '12.2')),
            'null'         => array(null, null, array())
        );
    }

    /**
     * @dataProvider getSetValuesData
     */
    public function testSetValues($suffix, $data, $expectedPrices)
    {
        $columnInfo = $this->getMock('Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface');
        $columnInfo->expects($this->any())
            ->method('getSuffixes')
            ->will($this->returnValue(array($suffix)));

        $object = $this->getProductValueMock();

        if (!$suffix) {
            $this->builder->expects($this->once())
                ->method('removePricesNotInCurrency')
                ->with($object, array_keys($expectedPrices));
        }

        $this->builder->expects($this->exactly(count($expectedPrices)))
            ->method('addPriceForCurrency')
            ->will(
                $this->returnCallback(
                    function ($value, $currency) use ($expectedPrices) {
                        $price = $this->getMockForPrice($currency, $expectedPrices[$currency]);
                        $price->expects($this->once())
                            ->method('setData')
                            ->with($this->equalTo($expectedPrices[$currency]));

                        return $price;
                    }
                )
            );

        $this->transformer->setValue($object, $columnInfo, $data);
    }

    /**
     * @expectedException \Pim\Bundle\TransformBundle\Exception\PropertyTransformerException
     * @expectedExceptionMessage Malformed price: "15"
     */
    public function testMalformedPrice()
    {
        $columnInfo = $this->getMock('Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoInterface');
        $columnInfo->expects($this->any())
            ->method('getSuffixes')
            ->will($this->returnValue(array()));
        $object = $this->getProductValueMock();

        $this->transformer->setValue($object, $columnInfo, '12.O2 cur1,15');
    }

    protected function getMockForPrice($currency, $data = null)
    {
        $price = $this->getMock('Pim\Bundle\CatalogBundle\Model\ProductPrice');
        $price->expects($this->any())
            ->method('getCurrency')
            ->will($this->returnValue($currency));
        if (null !== $data) {
            $price->expects($this->any())
                ->method('getData')
                ->will($this->returnValue($data));
        }

        return $price;
    }

    protected function getProductValueMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Model\ProductValueInterface')
            ->setMethods(
                array(
                    'setPrices',
                    'addPriceForCurrency',
                    '__toString',
                    'setData',
                    'getData',
                    'getAttribute',
                    'getEntity'
                )
            )
            ->getMock();
    }
}
