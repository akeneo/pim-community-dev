<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Entity;

use Pim\Bundle\ProductBundle\Entity\CategoryTranslation;

use Pim\Bundle\ProductBundle\Entity\Product;

use Pim\Bundle\ProductBundle\Entity\Category;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CategoryTest extends \PHPUnit_Framework_TestCase
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
        $category = new Category();

        // assert instance and implementation
        $this->assertEntity($category);
        $this->assertInstanceOf('\Oro\Bundle\SegmentationTreeBundle\Entity\AbstractSegment', $category);
        $this->assertInstanceOf('\Gedmo\Translatable\Translatable', $category);

        // assert object properties
        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $category->getChildren());
        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $category->getProducts());
        $this->assertCount(0, $category->getChildren());
        $this->assertCount(0, $category->getProducts());
    }

    /**
     * Test add/remove/get products methods
     */
    public function testGetProducts()
    {
        $category = new Category();
        $product1 = $this->createProduct();
        $product2 = $this->createProduct();

        // assert adding
        $this->assertEntity($category->addProduct($product1));
        $category->addProduct($product2);
        $this->assertCount(2, $category->getProducts());

        // assert removing
        $this->assertEntity($category->removeProduct($product1));
        $this->assertCount(1, $category->getProducts());
    }

    /**
     * Test related method
     * Just a call to prevent fatal errors (no way to verify value is set)
     */
    public function testSetTranslatableLocale()
    {
        $category = new Category();
        $this->assertEntity($category->setTranslatableLocale('en_US'));
    }

    /**
     * Test getter/setter for code property
     */
    public function testCode()
    {
        $category = new Category();

        // assert getter
        $this->assertNull($category->getCode());

        // assert setter
        $testCode = 'test-code';
        $this->assertEntity($category->setCode($testCode));
        $this->assertEquals($testCode, $category->getCode());
    }

    /**
     * Test is/setter for dynamic property
     */
    public function testDynamic()
    {
        $category = new Category();

        // assert getter
        $this->assertFalse($category->isDynamic());

        // assert setter
        $testIsDynamic = true;
        $this->assertEntity($category->setDynamic($testIsDynamic));
        $this->assertEquals($testIsDynamic, $category->isDynamic());
    }

    /**
     * Test getter/setter for translations property
     */
    public function testTranslations()
    {
        $category = new Category();
        $this->assertCount(0, $category->getTranslations());

        // Change value and assert new
        $newTranslation = new CategoryTranslation();
        $this->assertEntity($category->addTranslation($newTranslation));
        $this->assertCount(1, $category->getTranslations());
        $this->assertInstanceOf(
            'Pim\Bundle\ProductBundle\Entity\CategoryTranslation',
            $category->getTranslations()->first()
        );

        $category->addTranslation($newTranslation);
        $this->assertCount(1, $category->getTranslations());

        $this->assertEntity($category->removeTranslation($newTranslation));
        $this->assertCount(0, $category->getTranslations());
    }

    /**
     * Assert entity
     * @param Pim\Bundle\ProductBundle\Entity\Category $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\Category', $entity);
    }
}
