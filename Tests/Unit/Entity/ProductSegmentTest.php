<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Entity;

use Pim\Bundle\ProductBundle\Entity\Product;

use Pim\Bundle\ProductBundle\Entity\ProductSegment;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductSegmentTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Create a mock of flexible product entity
     *
     * @return \Pim\Bundle\ProductBundle\Entity\Product
     */
    protected function createProduct()
    {
        return new Product();
    }

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $segment = new ProductSegment();

        // assert instance and implementation
        $this->assertEntity($segment);
        $this->assertInstanceOf('\Oro\Bundle\SegmentationTreeBundle\Entity\AbstractSegment', $segment);
        $this->assertInstanceOf('\Gedmo\Translatable\Translatable', $segment);

        // assert object properties
        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $segment->getChildren());
        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $segment->getProducts());
        $this->assertCount(0, $segment->getChildren());
        $this->assertCount(0, $segment->getProducts());
    }

    /**
     * Test add/remove/get products methods
     */
    public function testGetProducts()
    {
        $segment = new ProductSegment();
        $product1 = $this->createProduct();
        $product2 = $this->createProduct();

        // assert adding
        $this->assertEntity($segment->addProduct($product1));
        $segment->addProduct($product2);
        $this->assertCount(2, $segment->getProducts());

        // assert removing
        $this->assertEntity($segment->removeProduct($product1));
        $this->assertCount(1, $segment->getProducts());
    }

    /**
     * Test related method
     * Just a call to prevent fatal errors (no way to verify value is set)
     */
    public function testSetTranslatableLocale()
    {
        $segment = new ProductSegment();
        $this->assertEntity($segment->setTranslatableLocale('en_US'));
    }

    /**
     * Test getter/setter for code property
     */
    public function testCode()
    {
        $segment = new ProductSegment();

        // assert getter
        $this->assertNull($segment->getCode());

        // assert setter
        $testCode = 'test-code';
        $this->assertEntity($segment->setCode($testCode));
        $this->assertEquals($testCode, $segment->getCode());
    }

    /**
     * Test getter/setter for isDynamic property
     */
    public function testIsDynamic()
    {
        $segment = new ProductSegment();

        // assert getter
        $this->assertFalse($segment->getIsDynamic());

        // assert setter
        $testIsDynamic = true;
        $this->assertEntity($segment->setIsDynamic($testIsDynamic));
        $this->assertEquals($testIsDynamic, $segment->getIsDynamic());
    }

    /**
     * Assert entity
     * @param Pim\Bundle\ProductBundle\Entity\ProductSegment $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\ProductSegment', $entity);
    }
}
