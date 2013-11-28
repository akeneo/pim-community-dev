<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer\Property;

use Pim\Bundle\ImportExportBundle\Transformer\Property\PricesTransformer;
use Pim\Bundle\CatalogBundle\Entity\ProductPrice;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PricesTransformerTest extends \PHPUnit_Framework_TestCase
{
    protected $transformer;
    protected $currencyManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->currencyManager = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\CurrencyManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->currencyManager
            ->expects($this->once())
            ->method('getActiveCodes')
            ->will($this->returnValue(array('EUR', 'USD', 'CAD')));
        $this->transformer = new PricesTransformer($this->currencyManager);
    }

    /**
     * Test related method
     */
    public function testTransform()
    {
        $this->assertEquals(array(), $this->transformer->transform(''));
        $this->assertEquals(array(), $this->transformer->transform(' '));
        $this->assertEquals(array('EUR' => $this->getPrice(15.2, 'EUR')), $this->transformer->transform(' 15.20 EUR'));
        $this->assertEquals(
            array('EUR' => $this->getPrice(15.2, 'EUR'), 'USD' => $this->getPrice(45, 'USD')),
            $this->transformer->transform(' 15.20 EUR, 45 USD ')
        );
    }

    /**
     * @expectedException Pim\Bundle\ImportExportBundle\Exception\PropertyTransformerException
     * @expectedExceptionMessage Malformed price: "15"
     */
    public function testUnvalidTransform()
    {
        $this->transformer->transform(' 15 ');
    }

    /**
     * @expectedException Pim\Bundle\ImportExportBundle\Exception\PropertyTransformerException
     * @expectedExceptionMessage Currency "CHF" is not active
     */
    public function testInactiveCurrenctTransform()
    {
        $this->transformer->transform(' 15 USD, 30 CHF');
    }

    /**
     * @param float  $data
     * @param string $currency
     *
     * @return ProductPrice
     */
    protected function getPrice($data, $currency)
    {
        $price = new ProductPrice();

        return $price->setData($data)->setCurrency($currency);
    }
}
