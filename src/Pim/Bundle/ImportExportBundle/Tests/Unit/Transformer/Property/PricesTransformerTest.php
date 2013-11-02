<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Transformer\Property;

use Pim\Bundle\ImportExportBundle\Transformer\Property\PricesTransformer;

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

    public function testTransform()
    {
        $this->assertEquals(array(), $this->transformer->transform(''));
        $this->assertEquals(array(), $this->transformer->transform(' '));
        $this->assertEquals(array('EUR' => 15.2), $this->transformer->transform(' 15.20 EUR'));
        $this->assertEquals(array('EUR' => 15.2, 'USD' => 45), $this->transformer->transform(' 15.20 EUR, 45 USD '));
    }

    /**
     * @expectedException Pim\Bundle\ImportExportBundle\Exception\InvalidValueException
     * @expectedExceptionMessage Malformed price: "15"
     */
    public function testUnvalidTransform()
    {
        $this->transformer->transform(' 15 ');
    }

    /**
     * @expectedException Pim\Bundle\ImportExportBundle\Exception\InvalidValueException
     * @expectedExceptionMessage Currency "CHF" is not active
     */
    public function testInactiveCurrenctTransform()
    {
        $this->transformer->transform(' 15 USD, 30 CHF');
    }
}
