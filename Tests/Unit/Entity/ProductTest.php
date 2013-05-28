<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Entity;

use Pim\Bundle\ProductBundle\Entity\Product;
use Pim\Bundle\ProductBundle\Entity\ProductFamily;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
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
        $product = new Product();
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\Product', $product);
    }

    /**
     * Test getter/setter for sku property
     */
    public function testGetSetSku()
    {
        $product = new Product();
        $this->assertEmpty($product->getSku());

        // Change value and assert new
        $newSku = 'test-sku';
        $product->setSku($newSku);
        $this->assertEquals($newSku, $product->getSku());
    }

    /**
     * Test getter/setter for productFamily property
     */
    public function testGetSetProductFamily()
    {
        $product = new Product();
        $this->assertEmpty($product->getProductFamily());

        // Change value and assert new
        $newProductFamily = new ProductFamily();
        $product->setProductFamily($newProductFamily);
        $this->assertEquals($newProductFamily, $product->getProductFamily());

        $product->setProductFamily(null);
        $this->assertNull($product->getProductFamily());
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
        $product->setSku('foo');
        $this->assertEquals('foo', $product->getLabel());
    }

    public function testAttributeLabel()
    {
        $attributeAsLabel = $this->getAttributeMock();
        $family           = $this->getFamilyMock($attributeAsLabel);
        $value            = $this->getValueMock($attributeAsLabel, 'bar');

        $product = new Product();
        $product->setSku('foo');
        $product->setProductFamily($family);
        $product->addValue($value);

        $this->assertEquals('bar', $product->getLabel());
    }

    public function testNullValuedAttributeLabel()
    {
        $attributeAsLabel = $this->getAttributeMock();
        $family           = $this->getFamilyMock($attributeAsLabel);
        $value            = $this->getValueMock($attributeAsLabel, null);

        $product = new Product();
        $product->setSku('foo');
        $product->setProductFamily($family);
        $product->addValue($value);

        $this->assertEquals('foo', $product->getLabel());
    }

    public function testEmptyStringValuedAttributeLabel()
    {
        $attributeAsLabel = $this->getAttributeMock();
        $family           = $this->getFamilyMock($attributeAsLabel);
        $value            = $this->getValueMock($attributeAsLabel, '');

        $product = new Product();
        $product->setSku('foo');
        $product->setProductFamily($family);
        $product->addValue($value);

        $this->assertEquals('foo', $product->getLabel());
    }

    public function testNullAttributeLabel()
    {
        $attribute = $this->getAttributeMock();
        $family    = $this->getFamilyMock(null);
        $value     = $this->getValueMock($attribute, 'bar');

        $product = new Product();
        $product->setSku('foo');
        $product->setProductFamily($family);
        $product->addValue($value);

        $this->assertEquals('foo', $product->getLabel());
    }

    private function getAttributeMock($group = null)
    {
        $attribute = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductAttribute', array('getGroup'));

        $attribute->expects($this->any())
                  ->method('getGroup')
                  ->will($this->returnValue($group));

        return $attribute;
    }

    private function getValueMock($attribute, $data = null)
    {
        $value = $this->getMock('Pim\Bundle\ProductBundle\Entity\ProductValue', array('getAttribute', 'getData'));

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
        $group = $this->getMock('Pim\Bundle\ProductBundle\Entity\AttributeGroup', array('getSortOrder', 'getName'));

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
