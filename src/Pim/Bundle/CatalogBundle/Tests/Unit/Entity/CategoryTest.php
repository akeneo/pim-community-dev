<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Entity;

use Pim\Bundle\CatalogBundle\Model\CategoryTranslation;
use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\CatalogBundle\Model\Category;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Pim\Bundle\CatalogBundle\Model\Category
     */
    protected $category;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->category = new Category();
    }

    /**
     * Create a mock of flexible product entity
     *
     * @return \Pim\Bundle\CatalogBundle\Model\Product
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
        // assert instance and implementation
        $this->assertEntity($this->category);
        $this->assertInstanceOf('\Oro\Bundle\SegmentationTreeBundle\Entity\AbstractSegment', $this->category);
        $this->assertInstanceOf('\Pim\Bundle\TranslationBundle\Entity\TranslatableInterface', $this->category);

        // assert object properties
        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $this->category->getChildren());
        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $this->category->getProducts());
        $this->assertCount(0, $this->category->getChildren());
        $this->assertCount(0, $this->category->getProducts());
    }

    /**
     * Test add/remove/get products methods
     */
    public function testGetProducts()
    {
        $product1 = $this->createProduct();
        $product2 = $this->createProduct();

        $this->assertFalse($this->category->hasProducts());
        $this->assertEquals(0, $this->category->getProductsCount());

        // assert adding
        $this->assertEntity($this->category->addProduct($product1));
        $this->category->addProduct($product2);
        $this->assertCount(2, $this->category->getProducts());
        $this->assertEquals(2, $this->category->getProductsCount());
        $this->assertTrue($this->category->hasProducts());

        // assert removing
        $this->assertEntity($this->category->removeProduct($product1));
        $this->assertCount(1, $this->category->getProducts());
        $this->assertEquals(1, $this->category->getProductsCount());
        $this->assertTrue($this->category->hasProducts());

        // assert product entity
        $products = $this->category->getProducts();
        foreach ($products as $product) {
            $this->assertProductEntity($product);
        }
    }

    /**
     * Test related method
     * Just a call to prevent fatal errors (no way to verify value is set)
     */
    public function testSetLocale()
    {
        $this->assertEntity($this->category->setLocale('en_US'));
    }

    /**
     * Test getter/setter for code property
     */
    public function testCode()
    {
        // assert getter
        $this->assertNull($this->category->getCode());

        // assert setter
        $testCode = 'test-code';
        $this->assertEntity($this->category->setCode($testCode));
        $this->assertEquals($testCode, $this->category->getCode());
    }

    /**
     * Test getter/setter for label property
     */
    public function testGetSetLabel()
    {
        // Change value and assert new
        $newCode = 'code';
        $expectedCode = '['. $newCode .']';
        $this->category->setCode($newCode);
        $this->assertEquals($expectedCode, $this->category->getLabel());

        $newLabel = 'test-label';
        $this->assertEntity($this->category->setLocale('en_US'));
        $this->assertEntity($this->category->setLabel($newLabel));
        $this->assertEquals($newLabel, $this->category->getLabel());

        // if no translation, assert the expected code is returned
        $this->category->setLocale('fr_FR');
        $this->assertEquals($expectedCode, $this->category->getLabel());

        // if empty translation, assert the expected code is returned
        $this->category->setLabel('');
        $this->assertEquals($expectedCode, $this->category->getLabel());
    }

    /**
     * Test for __toString method
     */
    public function testToString()
    {
        // Change value and assert new
        $newCode = 'code';
        $expectedCode = '['. $newCode .']';
        $this->category->setCode($newCode);
        $this->assertEquals($expectedCode, $this->category->__toString());

        $newLabel = 'test-label';
        $this->assertEntity($this->category->setLocale('en_US'));
        $this->assertEntity($this->category->setLabel($newLabel));
        $this->assertEquals($newLabel, $this->category->__toString());

        // if no translation, assert the expected code is returned
        $this->category->setLocale('fr_FR');
        $this->assertEquals($expectedCode, $this->category->__toString());

        // if empty translation, assert the expected code is returned
        $this->category->setLabel('');
        $this->assertEquals($expectedCode, $this->category->__toString());
    }

    /**
     * Test getter/setter for translations property
     */
    public function testTranslations()
    {
        $this->assertCount(0, $this->category->getTranslations());

        // Change value and assert new
        $newTranslation = $this->createCategoryTranslation();
        $this->assertEntity($this->category->addTranslation($newTranslation));
        $this->assertCount(1, $this->category->getTranslations());
        $this->assertInstanceOf(
            'Pim\Bundle\CatalogBundle\Model\CategoryTranslation',
            $this->category->getTranslations()->first()
        );

        $this->category->addTranslation($newTranslation);
        $this->assertCount(1, $this->category->getTranslations());

        $this->assertEntity($this->category->removeTranslation($newTranslation));
        $this->assertCount(0, $this->category->getTranslations());
    }

    /**
     * Test related method
     * Only test if getCreated return a value or null. Setter is not accessible.
     */
    public function testGetCreated()
    {
        $this->assertNull($this->category->getCreated());
    }

    /**
     * Assert entity
     * @param Pim\Bundle\CatalogBundle\Model\Category $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Model\Category', $entity);
    }

    /**
     * Create CategoryTranslation entity
     *
     * @return \Pim\Bundle\CatalogBundle\Model\CategoryTranslation
     */
    protected function createCategoryTranslation()
    {
        return new CategoryTranslation();
    }

    /**
     * Assert product entity
     *
     * @param Pim\Bundle\CatalogBundle\Model\Product $entity
     */
    protected function assertProductEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Model\Product', $entity);
    }
}
