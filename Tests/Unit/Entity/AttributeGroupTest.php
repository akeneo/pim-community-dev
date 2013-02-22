<?php
namespace Pim\Bundle\ProductBundle\Tests\Unit\Entity;

use Pim\Bundle\ProductBundle\Entity\AttributeGroup;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AttributeGroupTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $group = new AttributeGroup();
        $this->assertEntity($group);
    }

    /**
     * Test getter/setter for id property
     */
    public function testId()
    {
        $group = new AttributeGroup();
        $this->assertEmpty($group->getId());

        // Change value and assert new
        $newId = 7;
        $this->assertEntity($group->setId($newId));
        $this->assertEquals($newId, $group->getId());
    }

    /**
     * Test getter/setter for name property
     */
    public function testName()
    {
        $group = new AttributeGroup();
        $this->assertEmpty($group->getName());

        // Change value and assert new
        $newName = 'test-name';
        $this->assertEntity($group->setName($newName));
        $this->assertEquals($newName, $group->getName());
    }

    /**
     * Test getter/setter for sort order property
     */
    public function testSortOrder()
    {
        $group = new AttributeGroup();
        $this->assertEmpty($group->getSortOrder());

        // Change value and assert new
        $newOrder = 5;
        $this->assertEntity($group->setSortOrder($newOrder));
        $this->assertEquals($newOrder, $group->getSortOrder());
    }

    /**
     * Test getter/setter for created property
     */
    public function testCreated()
    {
        $group = new AttributeGroup();
        $this->assertEmpty($group->getCreated());

        // Change value and assert new
        $newCreated = new \Datetime();
        $this->assertEntity($group->setCreated($newCreated));
        $this->assertEquals($newCreated, $group->getCreated());
    }

    /**
     * Test getter/setter for updated property
     */
    public function testUpdated()
    {
        $group = new AttributeGroup();
        $this->assertEmpty($group->getUpdated());

        // Change value and assert new
        $newUpdated = new \Datetime();
        $this->assertEntity($group->setUpdated($newUpdated));
        $this->assertEquals($newUpdated, $group->getUpdated());
    }

    /**
     * Assert entity
     * @param Pim\Bundle\ProductBundle\Entity\AttributeGroup $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\AttributeGroup', $entity);
    }
}
