<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Entity;

use Pim\Bundle\ProductBundle\Entity\Product;
use Pim\Bundle\ProductBundle\Entity\Family;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test related method
     */
    public function testConstruct()
    {
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\Product', new Product());
    }

    /**
     * Test getter/setter for family property
     */
    public function testGetSetFamily()
    {
        $product = new Product();
        $this->assertEmpty($product->getFamily());

        // Change value and assert new
        $newFamily = new Family();
        $product->setFamily($newFamily);
        $this->assertEquals($newFamily, $product->getFamily());

        $product->setFamily(null);
        $this->assertNull($product->getFamily());
    }

    public function testGetAttributes()
    {
        $product    = new Product();
        $attributes = array(
            $this->getAttributeMock(),
            $this->getAttributeMock(),
            $this->getAttributeMock(),
        );

        foreach ($attributes as $attribute) {
            $product->addValue($this->getValueMock($attribute));
        }

        $this->assertEquals($attributes, $product->getAttributes());
    }

    public function testGetGroups()
    {
        $product = new Product();
        $groups  = array(
            $otherGroup   = $this->getGroupMock('Other', -1),
            $generalGroup = $this->getGroupMock('General', 0),
            $alphaGroup   = $this->getGroupMock('Alpha', 20),
            $betaGroup    = $this->getGroupMock('Beta', 10),
        );

        foreach ($groups as $group) {
            $product->addValue($this->getValueMock($this->getAttributeMock($group)));
        }

        $this->markTestIncomplete('usort(): Array was modified by user comparison function is a false positive');

        $groups = $product->getOrderedGroups();
        $this->assertSame(4, count($groups));
        $this->assertSame($generalGroup, current($groups));
        $this->assertSame($betaGroup, next($groups));
        $this->assertSame($alphaGroup, next($groups));
        $this->assertSame($otherGroup, next($groups));
    }

    public function testSkuLabel()
    {
        $product = new Product();
        $product->setId(5);
        $this->assertEquals(5, $product->getLabel());
    }

    public function testAttributeLabel()
    {
        $attributeAsLabel = $this->getAttributeMock();
        $family           = $this->getFamilyMock($attributeAsLabel);
        $value            = $this->getValueMock($attributeAsLabel, 'bar');

        $product = new Product();
        $product->setId(10);
        $product->setFamily($family);
        $product->addValue($value);

        $this->assertEquals('bar', $product->getLabel());
    }

    public function testNullValuedAttributeLabel()
    {
        $attributeAsLabel = $this->getAttributeMock();
        $family           = $this->getFamilyMock($attributeAsLabel);
        $value            = $this->getValueMock($attributeAsLabel, null);

        $product = new Product();
        $product->setId(25);
        $product->setFamily($family);
        $product->addValue($value);

        $this->assertEquals(25, $product->getLabel());
    }

    public function testEmptyStringValuedAttributeLabel()
    {
        $attributeAsLabel = $this->getAttributeMock();
        $family           = $this->getFamilyMock($attributeAsLabel);
        $value            = $this->getValueMock($attributeAsLabel, '');

        $product = new Product();
        $product->setId(38);
        $product->setFamily($family);
        $product->addValue($value);

        $this->assertEquals(38, $product->getLabel());
    }

    public function testNullAttributeLabel()
    {
        $attribute = $this->getAttributeMock();
        $family    = $this->getFamilyMock(null);
        $value     = $this->getValueMock($attribute, 'bar');

        $product = new Product();
        $product->setId(53);
        $product->setFamily($family);
        $product->addValue($value);

        $this->assertEquals(53, $product->getLabel());
    }

    public function testIsSetEnabled()
    {
        $product = new Product();
        $this->assertTrue($product->isEnabled());

        $product->setEnabled(false);
        $this->assertFalse($product->isEnabled());
    }

    public function testGetIdentifier()
    {
        $product    = new Product;
        $identifier = $this->getValueMock($this->getAttributeMock(null, 'pim_product_identifier'));
        $name       = $this->getValueMock($this->getAttributeMock());

        $product->addValue($identifier);
        $product->addValue($name);

        $this->assertSame($identifier, $product->getIdentifier());
    }

    /**
     * @expectedException Pim\Bundle\ProductBundle\Exception\MissingIdentifierException
     */
    public function testThrowExceptionIfNoIdentifier()
    {
        $product = new Product;
        $name    = $this->getValueMock($this->getAttributeMock());

        $product->addValue($name);

        $product->getIdentifier();
    }

    private function getAttributeMock($group = null, $type = 'pim_product_text')
    {
        $attribute = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductAttribute');

        $attribute->expects($this->any())
                  ->method('getVirtualGroup')
                  ->will($this->returnValue($group));

        $attribute->expects($this->any())
                  ->method('getAttributeType')
                  ->will($this->returnValue($type));

        return $attribute;
    }

    private function getValueMock($attribute, $data = null)
    {
        $value = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductValue');

        $value->expects($this->any())
              ->method('getAttribute')
              ->will($this->returnValue($attribute));

        $value->expects($this->any())
              ->method('getData')
              ->will($this->returnValue($data));

        return $value;
    }

    private function getGroupMock($name, $sortOrder)
    {
        $group = $this->getMock('Pim\Bundle\ProductBundle\Entity\AttributeGroup');

        $group->expects($this->any())
              ->method('getSortOrder')
              ->will($this->returnValue($sortOrder));

        $group->expects($this->any())
              ->method('getName')
              ->will($this->returnValue($name));

        return $group;
    }

    private function getFamilyMock($attributeAsLabel)
    {
        $attribute = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductAttribute', array('getAttributeAsLabel'));

        $attribute->expects($this->any())
                  ->method('getAttributeAsLabel')
                  ->will($this->returnValue($attributeAsLabel));

        return $attribute;
    }
}
