<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Entity;

use Pim\Bundle\CatalogBundle\Entity\Product;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Entity\VariantGroup;
use Pim\Bundle\CatalogBundle\Entity\VariantGroupTranslation;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Pim\Bundle\CatalogBundle\Entity\VariantGroup
     */
    protected $variant;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->variant = new VariantGroup();
    }

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $this->assertEntity($this->variant);

        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $this->variant->getAttributes());
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $this->variant->getProducts());
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $this->variant->getTranslations());

        $this->assertCount(0, $this->variant->getAttributes());
        $this->assertCount(0, $this->variant->getProducts());
        $this->assertCount(0, $this->variant->getTranslations());
    }

    /**
     * Test getter/setter for code property
     */
    public function testGetSetCode()
    {
        $this->assertEmpty($this->variant->getCode());

        // Change value and assert new
        $newCode = 'test-code';
        $this->assertEntity($this->variant->setCode($newCode));
        $this->assertEquals($newCode, $this->variant->getCode());
    }

    /**
     * Test getter/setter for label property
     */
    public function testGetSetLabel()
    {
        // Change value and assert new
        $newCode = 'code';
        $expectedCode = '['. $newCode .']';
        $this->variant->setCode($newCode);
        $this->assertEquals($expectedCode, $this->variant->getLabel());

        $newLabel = 'test-label';
        $this->assertEntity($this->variant->setLocale('en_US'));
        $this->assertEntity($this->variant->setLabel($newLabel));
        $this->assertEquals($newLabel, $this->variant->getLabel());

        // if no translation, assert the expected code is returned
        $this->variant->setLocale('fr_FR');
        $this->assertEquals($expectedCode, $this->variant->getLabel());

        // if empty translation, assert the expected code is returned
        $this->variant->setLabel('');
        $this->assertEquals($expectedCode, $this->variant->getLabel());
    }

    /**
     * Test for __toString method
     */
    public function testToString()
    {
        // Change value and assert new
        $newCode = 'toStringCode';
        $expectedCode = '['. $newCode .']';
        $this->variant->setCode($newCode);
        $this->assertEquals($expectedCode, $this->variant->__toString());

        $newLabel = 'toStringLabel';
        $this->assertEntity($this->variant->setLocale('en_US'));
        $this->assertEntity($this->variant->setLabel($newLabel));
        $this->assertEquals($newLabel, $this->variant->__toString());

        // if no translation, assert the expected code is returned
        $this->variant->setLocale('fr_FR');
        $this->assertEquals($expectedCode, $this->variant->__toString());

        // if empty translation, assert the expected code is returned
        $this->variant->setLabel('');
        $this->assertEquals($expectedCode, $this->variant->__toString());
    }

    /**
     * Test getter/add/remove for attributes property
     */
    public function testGetAddRemoveAttribute()
    {
        // Change value and assert new
        $newAttribute = new ProductAttribute();
        $this->assertEntity($this->variant->addAttribute($newAttribute));
        $this->assertInstanceOf(
            'Pim\Bundle\CatalogBundle\Entity\ProductAttribute',
            $this->variant->getAttributes()->first()
        );

        $this->assertEntity($this->variant->removeAttribute($newAttribute));
        $this->assertNotInstanceOf(
            'Pim\Bundle\CatalogBundle\Entity\ProductAttribute',
            $this->variant->getAttributes()->first()
        );
        $this->assertCount(0, $this->variant->getAttributes());
    }

    /**
     * Test getter/setter/add/remove for products property
     */
    public function testGetSetAddRemoveProducts()
    {
        // Change value and assert new
        $newProduct = new Product();
        $this->assertEntity($this->variant->addProduct($newProduct));
        $this->assertInstanceOf(
            'Pim\Bundle\CatalogBundle\Entity\Product',
            $this->variant->getProducts()->first()
        );

        $this->assertEntity($this->variant->removeProduct($newProduct));
        $this->assertNotInstanceOf(
            'Pim\Bundle\CatalogBundle\Entity\Product',
            $this->variant->getProducts()->first()
        );
        $this->assertCount(0, $this->variant->getProducts());

        // Use setter and assert getProducts result
        $newProduct1 = new Product();
        $expectedProducts = array($newProduct, $newProduct1);

        $this->assertEntity($this->variant->setProducts($expectedProducts));
        $this->assertCount(2, $this->variant->getProducts());
    }

    /**
     * Test getter/setter for translations property
     */
    public function testTranslations()
    {
        $this->assertCount(0, $this->variant->getTranslations());

        // Change value and assert new
        $newTranslation = new VariantGroupTranslation();
        $this->assertEntity($this->variant->addTranslation($newTranslation));
        $this->assertCount(1, $this->variant->getTranslations());
        $this->assertInstanceOf(
            'Pim\Bundle\CatalogBundle\Entity\VariantGroupTranslation',
            $this->variant->getTranslations()->first()
        );

        $this->variant->addTranslation($newTranslation);
        $this->assertCount(1, $this->variant->getTranslations());

        $this->assertEntity($this->variant->removeTranslation($newTranslation));
        $this->assertCount(0, $this->variant->getTranslations());
    }

    /**
     * Assert entity
     *
     * @param Pim\Bundle\CatalogBundle\Entity\VariantGroup $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\VariantGroup', $entity);
    }
}
