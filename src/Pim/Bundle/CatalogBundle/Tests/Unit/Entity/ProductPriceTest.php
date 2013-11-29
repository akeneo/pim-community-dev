<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Entity;

use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductPrice
     */
    protected $price;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->price = new ProductPrice();
    }

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $this->assertEntity($this->price);
        $this->assertNull($this->price->getData());
        $this->assertNull($this->price->getCurrency());

        // construct with data but without currency
        $expectedData = 'test-data';
        $this->price = new ProductPrice($expectedData);
        $this->assertEntity($this->price);
        $this->assertEquals($expectedData, $this->price->getData());
        $this->assertNull($this->price->getCurrency());

        // construct with currency but without data
        $expectedCurrency = 'test-currency';
        $this->price = new ProductPrice(null, $expectedCurrency);
        $this->assertEntity($this->price);
        $this->assertNull($this->price->getData());
        $this->assertEquals($expectedCurrency, $this->price->getCurrency());

        // construct with data and currency
        $this->price = new ProductPrice($expectedData, $expectedCurrency);
        $this->assertEntity($this->price);
        $this->assertEquals($expectedData, $this->price->getData());
        $this->assertEquals($expectedCurrency, $this->price->getCurrency());
    }

    /**
     * Test getter/setter for id property
     */
    protected function assertGetSetId()
    {
        $this->assertNull($this->price->getId());

        $expectedId = 5;

        $this->assertEntity($this->price->setId($expectedId));
        $this->assertEquals($expectedId, $this->price->getId());
    }

    /**
     * Test getter/setter for data property
     */
    public function testGetSetData()
    {
        $expectedData = 'test-data';

        $this->assertNull($this->price->getData());
        $this->assertEntity($this->price->setData($expectedData));
        $this->assertEquals($expectedData, $this->price->getData());
    }

    /**
     * Test getter/setter for currency property
     */
    public function testGetSetCurrency()
    {
        $expectedCurrency = 'test-currency';

        $this->assertNull($this->price->getCurrency());
        $this->assertEntity($this->price->setCurrency($expectedCurrency));
        $this->assertEquals($expectedCurrency, $this->price->getCurrency());
    }

    /**
     * Test getter/setter for value property
     */
    public function testGetSetValue()
    {
        $value = new ProductValue();

        $this->assertNull($this->price->getValue());
        $this->assertEntity($this->price->setValue($value));
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Model\ProductValue', $this->price->getValue());
    }

    /**
     * Assert entity
     * @param ProductPrice $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Model\ProductPrice', $entity);
    }
}
