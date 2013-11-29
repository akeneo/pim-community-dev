<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Entity;

use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\CatalogBundle\Model\Group;
use Pim\Bundle\CatalogBundle\Model\GroupTranslation;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Group
     */
    protected $group;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->group = new Group();
    }

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $this->assertEntity($this->group);

        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $this->group->getAttributes());
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $this->group->getProducts());
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $this->group->getTranslations());

        $this->assertCount(0, $this->group->getAttributes());
        $this->assertCount(0, $this->group->getProducts());
        $this->assertCount(0, $this->group->getTranslations());
    }

    /**
     * Test getter for id property
     */
    public function testId()
    {
        $this->assertEmpty($this->group->getId());
    }

    /**
     * Test getter/setter for code property
     */
    public function testGetSetCode()
    {
        $this->assertEmpty($this->group->getCode());

        // Change value and assert new
        $newCode = 'test-code';
        $this->assertEntity($this->group->setCode($newCode));
        $this->assertEquals($newCode, $this->group->getCode());
    }

    /**
     * Test getter/setter for label property
     */
    public function testGetSetLabel()
    {
        // Change value and assert new
        $newCode = 'code';
        $expectedCode = '['. $newCode .']';
        $this->group->setCode($newCode);
        $this->assertEquals($expectedCode, $this->group->getLabel());

        $newLabel = 'test-label';
        $this->assertEntity($this->group->setLocale('en_US'));
        $this->assertEntity($this->group->setLabel($newLabel));
        $this->assertEquals($newLabel, $this->group->getLabel());

        // if no translation, assert the expected code is returned
        $this->group->setLocale('fr_FR');
        $this->assertEquals($expectedCode, $this->group->getLabel());

        // if empty translation, assert the expected code is returned
        $this->group->setLabel('');
        $this->assertEquals($expectedCode, $this->group->getLabel());
    }

    /**
     * Test for __toString method
     */
    public function testToString()
    {
        // Change value and assert new
        $newCode = 'toStringCode';
        $expectedCode = '['. $newCode .']';
        $this->group->setCode($newCode);
        $this->assertEquals($expectedCode, $this->group->__toString());

        $newLabel = 'toStringLabel';
        $this->assertEntity($this->group->setLocale('en_US'));
        $this->assertEntity($this->group->setLabel($newLabel));
        $this->assertEquals($newLabel, $this->group->__toString());

        // if no translation, assert the expected code is returned
        $this->group->setLocale('fr_FR');
        $this->assertEquals($expectedCode, $this->group->__toString());

        // if empty translation, assert the expected code is returned
        $this->group->setLabel('');
        $this->assertEquals($expectedCode, $this->group->__toString());
    }

    /**
     * Test getter/add/remove for attributes property
     */
    public function testGetAddRemoveAttribute()
    {
        // Change value and assert new
        $newAttribute = new ProductAttribute();
        $this->assertEntity($this->group->addAttribute($newAttribute));
        $this->assertInstanceOf(
            'Pim\Bundle\CatalogBundle\Entity\ProductAttribute',
            $this->group->getAttributes()->first()
        );

        $this->assertEntity($this->group->removeAttribute($newAttribute));
        $this->assertNotInstanceOf(
            'Pim\Bundle\CatalogBundle\Entity\ProductAttribute',
            $this->group->getAttributes()->first()
        );
        $this->assertCount(0, $this->group->getAttributes());
    }

    /**
     * Test getter for attribute ids
     */
    public function testGetAttributeIds()
    {
        $expectedIds = array(1, 4, 6);
        foreach ($expectedIds as $id) {
            $attribute = new ProductAttribute();
            $attribute->setId($id);
            $this->group->addAttribute($attribute);
        }

        $this->assertEquals($expectedIds, $this->group->getAttributeIds());
    }

    /**
     * Test getter/setter/add/remove for products property
     */
    public function testGetSetAddRemoveProducts()
    {
        // Change value and assert new
        $newProduct = new Product();
        $this->assertEntity($this->group->addProduct($newProduct));
        $this->assertInstanceOf(
            'Pim\Bundle\CatalogBundle\Model\Product',
            $this->group->getProducts()->first()
        );

        $this->assertEntity($this->group->removeProduct($newProduct));
        $this->assertNotInstanceOf(
            'Pim\Bundle\CatalogBundle\Model\Product',
            $this->group->getProducts()->first()
        );
        $this->assertCount(0, $this->group->getProducts());

        // Use setter and assert getProducts result
        $newProduct1 = new Product();
        $expectedProducts = array($newProduct, $newProduct1);

        $this->assertEntity($this->group->setProducts($expectedProducts));
        $this->assertCount(2, $this->group->getProducts());
    }

    /**
     * Test getter/setter for translations property
     */
    public function testTranslations()
    {
        $this->assertCount(0, $this->group->getTranslations());

        // Change value and assert new
        $newTranslation = new GroupTranslation();
        $this->assertEntity($this->group->addTranslation($newTranslation));
        $this->assertCount(1, $this->group->getTranslations());
        $this->assertInstanceOf(
            'Pim\Bundle\CatalogBundle\Model\GroupTranslation',
            $this->group->getTranslations()->first()
        );

        $this->group->addTranslation($newTranslation);
        $this->assertCount(1, $this->group->getTranslations());

        $this->assertEntity($this->group->removeTranslation($newTranslation));
        $this->assertCount(0, $this->group->getTranslations());
    }

    /**
     * Assert entity
     *
     * @param Pim\Bundle\CatalogBundle\Model\Group $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Model\Group', $entity);
    }
}
