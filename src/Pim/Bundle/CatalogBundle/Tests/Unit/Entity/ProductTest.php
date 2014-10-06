<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Entity;

use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\Product;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Product
     */
    protected $product;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->product = new Product();
    }

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Model\Product', $this->product);

        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $this->product->getCategories());
        $this->assertCount(0, $this->product->getCategories());
    }

    /**
     * Test getter/setter for family property
     */
    public function testGetSetFamily()
    {
        $this->assertEmpty($this->product->getFamily());

        // Change value and assert new
        $newFamily = new Family();
        $this->product->setFamily($newFamily);
        $this->assertEquals($newFamily, $this->product->getFamily());

        $this->product->setFamily(null);
        $this->assertNull($this->product->getFamily());
    }

    /**
     * Test related method
     */
    public function testGetAttributes()
    {
        $attributes = array(
            $this->getAttributeMock(),
            $this->getAttributeMock(),
            $this->getAttributeMock(),
        );

        foreach ($attributes as $attribute) {
            $this->product->addValue($this->getValueMock($attribute));
        }

        $this->assertEquals($attributes, $this->product->getAttributes());
    }

    /**
     * Test related method
     */
    public function testGetGroups()
    {
        $groups = array(
            $otherGroup   = $this->getGroupMock(1, 'Other', 5),
            $generalGroup = $this->getGroupMock(2, 'General', 0),
            $alphaGroup   = $this->getGroupMock(3, 'Alpha', 20),
            $betaGroup    = $this->getGroupMock(4, 'Beta', 10),
        );

        foreach ($groups as $group) {
            $this->product->addValue($this->getValueMock($this->getAttributeMock($group)));
        }

        $groups = $this->product->getOrderedGroups();
        $this->assertSame(4, count($groups));
        $this->assertSame($generalGroup, current($groups));
        $this->assertSame($otherGroup, next($groups));
        $this->assertSame($betaGroup, next($groups));
        $this->assertSame($alphaGroup, next($groups));
    }

    /**
     * Test related method
     */
    public function testSkuLabel()
    {
        $sku = $this->getValueMock($this->getAttributeMock(null, 'pim_catalog_identifier'), 'foo-bar');
        $this->product->addValue($sku);

        $this->assertEquals('foo-bar', $this->product->getLabel());
    }

    /**
     * Test related method
     */
    public function testAttributeLabel()
    {
        $attributeAsLabel = $this->getAttributeMock();

        $family           = $this->getFamilyMock($attributeAsLabel);
        $value            = $this->getValueMock($attributeAsLabel, 'bar');

        $this->product->setId(10);
        $this->product->setFamily($family);
        $this->product->addValue($value);

        $this->assertEquals('bar', $this->product->getLabel());
    }

    /**
     * Test related method
     */
    public function testNullValuedAttributeLabel()
    {
        $attributeAsLabel = $this->getAttributeMock();
        $family           = $this->getFamilyMock($attributeAsLabel);
        $value            = $this->getValueMock($attributeAsLabel, null);

        $sku = $this->getValueMock($this->getAttributeMock(null, 'pim_catalog_identifier'), 'foo-bar');
        $this->product->addValue($sku);

        $this->product->setFamily($family);
        $this->product->addValue($value);

        $this->assertEquals('foo-bar', $this->product->getLabel());
    }

    /**
     * Test related method
     */
    public function testEmptyStringValuedAttributeLabel()
    {
        $attributeAsLabel = $this->getAttributeMock();
        $family           = $this->getFamilyMock($attributeAsLabel);
        $value            = $this->getValueMock($attributeAsLabel, '');

        $sku = $this->getValueMock($this->getAttributeMock(null, 'pim_catalog_identifier'), 'foo-bar');
        $this->product->addValue($sku);

        $this->product->setFamily($family);
        $this->product->addValue($value);

        $this->assertEquals('foo-bar', $this->product->getLabel());
    }

    /**
     * Test related method
     */
    public function testNullAttributeLabel()
    {
        $attribute = $this->getAttributeMock();
        $family    = $this->getFamilyMock(null);
        $value     = $this->getValueMock($attribute, 'bar');

        $sku = $this->getValueMock($this->getAttributeMock(null, 'pim_catalog_identifier'), 'foo-bar');
        $this->product->addValue($sku);

        $this->product->setFamily($family);
        $this->product->addValue($value);

        $this->assertEquals('foo-bar', $this->product->getLabel());
    }

    /**
     * Test related method
     */
    public function testIsSetEnabled()
    {
        $this->assertTrue($this->product->isEnabled());

        $this->product->setEnabled(false);
        $this->assertFalse($this->product->isEnabled());

        $this->product->setEnabled(true);
        $this->assertTrue($this->product->isEnabled());
    }

    /**
     * Test related method
     */
    public function testGetIdentifier()
    {
        $identifier = $this->getValueMock($this->getAttributeMock(null, 'pim_catalog_identifier'));
        $name       = $this->getValueMock($this->getAttributeMock());

        $this->product->addValue($identifier);
        $this->product->addValue($name);

        $this->assertSame($identifier, $this->product->getIdentifier());
    }

    /**
     * @expectedException Pim\Bundle\CatalogBundle\Exception\MissingIdentifierException
     */
    public function testThrowExceptionIfNoIdentifier()
    {
        $name    = $this->getValueMock($this->getAttributeMock());

        $this->product->addValue($name);

        $this->product->getIdentifier();
    }

    /**
     * Test getter/setter for group
     */
    public function testGetSetGroup()
    {
        $this->assertEquals(count($this->product->getGroups()), 0);

        // Change value and assert new
        $newGroup = new Group();
        $this->product->addGroup($newGroup);
        $this->assertEquals($newGroup, $this->product->getGroups()->first());

        $this->product->removeGroup($newGroup);
        $this->assertEmpty(count($this->product->getGroups()), 0);
    }

    /**
     * Test getter for media
     */
    public function testGetMedia()
    {
        $this->product->addValue(
            $this->getValueMock(
                $this->getAttributeMock(null, 'pim_catalog_identifier', 'sku')
            )
        );
        $this->product->addValue(
            $this->getValueMock(
                $this->getAttributeMock(null, 'pim_catalog_text', 'name')
            )
        );
        $this->product->addValue(
            $this->getValueMock(
                $this->getAttributeMock(null, 'pim_catalog_image', 'view'),
                $view = $this->getMediaMock()
            )
        );
        $this->product->addValue(
            $this->getValueMock(
                $this->getAttributeMock(null, 'pim_catalog_file', 'manual'),
                $manual = $this->getMediaMock()
            )
        );

        $this->assertEquals(
            array($view, $manual),
            $this->product->getMedia()
        );
    }

    /**
     * Create a group entity
     *
     * @param string $code
     *
     * @return Group
     */
    protected function createGroup($code)
    {
        $group = new Group();
        $group->setCode($code);

        return $group;
    }

    /**
     * @param mixed  $group
     * @param string $type
     * @param string $code
     *
     * @return \Pim\Bundle\CatalogBundle\Model\AbstractAttribute
     */
    protected function getAttributeMock($group = null, $type = 'pim_catalog_text', $code = null)
    {
        $attribute = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Attribute');

        $attribute->expects($this->any())
            ->method('getGroup')
            ->will($this->returnValue($group));

        $attribute->expects($this->any())
            ->method('getAttributeType')
            ->will($this->returnValue($type));

        $attribute->expects($this->any())
            ->method('getCode')
            ->will($this->returnValue($code));

        $attribute->expects($this->any())
            ->method('getData')
            ->will($this->returnValue('foo'));

        return $attribute;
    }

    /**
     * @param mixed $attribute
     * @param mixed $data
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductValue
     */
    protected function getValueMock($attribute, $data = null)
    {
        $value = $this->getMock('Pim\Bundle\CatalogBundle\Model\ProductValue');

        $value->expects($this->any())
              ->method('getAttribute')
              ->will($this->returnValue($attribute));

        $value->expects($this->any())
              ->method('getData')
              ->will($this->returnValue($data));

        $value->expects($this->any())
            ->method('isMatching')
            ->will($this->returnValue(true));

        return $value;
    }

    /**
     * @param integer $id
     * @param mixed   $name
     * @param integer $sortOrder
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\AttributeGroup
     */
    protected function getGroupMock($id, $name, $sortOrder)
    {
        $group = $this->getMock('Pim\Bundle\CatalogBundle\Entity\AttributeGroup');

        $group->expects($this->any())
              ->method('getId')
              ->will($this->returnValue($id));

        $group->expects($this->any())
              ->method('getSortOrder')
              ->will($this->returnValue($sortOrder));

        $group->expects($this->any())
              ->method('getName')
              ->will($this->returnValue($name));

        return $group;
    }

    /**
     * @param mixed $attributeAsLabel
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Family
     */
    protected function getFamilyMock($attributeAsLabel)
    {
        $family = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Family', array('getAttributeAsLabel'));

        $family->expects($this->any())
               ->method('getAttributeAsLabel')
               ->will($this->returnValue($attributeAsLabel));

        return $family;
    }

    /**
     * Assert tested entity
     *
     * @param Product $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Model\Product', $entity);
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Model\ProductMedia
     */
    protected function getMediaMock()
    {
        return $this->getMock('Pim\Bundle\CatalogBundle\Model\ProductMedia');
    }
}
