<?php

namespace Strixos\CatalogEavBundle\Tests\Entity;


use Strixos\CatalogEavBundle\Entity\ProductType;
use Strixos\CatalogEavBundle\Entity\Product;


/**
 * @author Romain Monceau @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTypeTest extends EntityTest
{
    /**
     * @var string
     */
    const PT_CODE_CHANGED_VALUE = 'azerty';
    
    /**
     * (non-documented)
     * @see EntityTest
     */
    protected function getEntityClassName()
    {
        return 'Strixos\CatalogEavBundle\Entity\ProductType';
    }
    
    /**
     * (non-documented)
     */
    public function setUp()
    {
        parent::setUp();
        
        // TODO : set values to default entity
//         $this->entity->setId(1);
//         $this->entity->setCode('pouic');
    }
    
    /**
     * test id attribute getter
     */
    public function testGetId()
    {
        $this->assertNull($this->entity->getId());
    }
    
    /**
     * Test code attribute accessors
     */
    public function testCodeAccessors()
    {
        $this->assertNull($this->entity->getCode());
        $this->entity->setCode(self::PT_CODE_CHANGED_VALUE);
        $this->assertEquals(self::PT_CODE_CHANGED_VALUE, $this->entity->getCode());
    }
    
    /**
     * Test product accessors (add, remove, get)
     */
    public function testProductAccessors()
    {
        $this->assertGetProducts(0);
        
        // add product
        $firstProduct = $this->createProduct();
        $this->entity->addProduct($firstProduct);
        $this->assertGetProducts(1);
        
        // add product
        $secondProduct = $this->createProduct();
        $this->entity->addProduct($secondProduct);
        $this->assertGetProducts(2);
        
        // verify first product is different of the second
        $productList = $this->entity->getProducts();
        $this->assertNotSame($productList->first(), $productList->last());
        
        // remove product
        $this->entity->removeProduct($firstProduct);
        $this->assertGetProducts(1);
        
        // verify first product is deleted and second already exists
        $productList = $this->entity->getProducts();
        $this->assertSame($productList->first(), $productList->last());
        $this->assertSame($productList->first(), $secondProduct);
        $this->assertNotSame($productList->first(), $firstProduct);
    }
    
    /**
     * Assert count of product list
     * @param integer $count
     */
    protected function assertGetProducts($count)
    {
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection',
        		$this->entity->getProducts());
        $this->assertCount($count, $this->entity->getProducts()->toArray());
    }
    
    /**
     * Remove a product non existant to the entity
     */
    public function testRemoveNonExistantProduct()
    {
        $this->assertGetProducts(0);
        // TODO ! access to first product.. -> exception ??! => must be tested
        
        // add product
        $firstProduct = $this->createProduct();
        $this->entity->addProduct($firstProduct);
        $this->assertGetProducts(1);
        
        // remove non-existant product
        $secondProduct = $this->createProduct();
        $this->entity->removeProduct($secondProduct);
        
        // assert count is equal
        $this->assertGetProducts(1);
    }
    
    /**
     * Remove a product already removed to the entity
     */
    public function testRemoveAlreadyRemovedProduct()
    {
        $this->assertGetProducts(0);
        
        // add product
        $firstProduct = $this->createProduct();
        $this->entity->addProduct($firstProduct);
        $this->assertGetProducts(1);
        
        // remove product
        $this->entity->removeProduct($firstProduct);
        $this->assertGetProducts(0);
        
        // remove already removed product
        $this->entity->removeProduct($firstProduct);
        $this->assertGetProducts(0);
    }
    
    /**
     * Add a product already added to the entity
     */
    public function testAddAlreadyAddedProduct()
    {
        $this->assertGetProducts(0);
        
        // create product
        $firstProduct = $this->createProduct();
        
        // add product
        $this->entity->addProduct($firstProduct);
        $this->assertGetProducts(1);
        
        // add already added product
        $this->entity->addProduct($firstProduct);
        $this->assertGetProducts(2);
        
        // remove product
        $this->entity->removeProduct($firstProduct);
        $this->assertGetProducts(1);
    }
    
    /**
     * Create an empty product entity
     * @return \Strixos\CatalogEavBundle\Entity\Product
     */
    protected function createProduct()
    {
        return new Product();
    }
}