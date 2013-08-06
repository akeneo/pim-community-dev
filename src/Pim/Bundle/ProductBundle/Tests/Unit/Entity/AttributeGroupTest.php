<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Entity;

use Pim\Bundle\ProductBundle\Entity\AttributeGroupTranslation;

use Pim\Bundle\ProductBundle\Entity\ProductAttribute;

use Pim\Bundle\ProductBundle\Entity\AttributeGroup;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
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

        // assert instance and implementation
        $this->assertEntity($group);
        $this->assertInstanceOf('\Oro\Bundle\FlexibleEntityBundle\Model\Behavior\TimestampableInterface', $group);
        $this->assertInstanceOf('\Pim\Bundle\TranslationBundle\Entity\TranslatableInterface', $group);

        // assert object properties
        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $group->getAttributes());
        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $group->getTranslations());
        $this->assertCount(0, $group->getAttributes());
        $this->assertCount(0, $group->getTranslations());
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
     * Test getter/setter for code property
     */
    public function testCode()
    {
        $group = new AttributeGroup();
        $this->assertEmpty($group->getCode());

        // Change value and assert new
        $newCode = 'test-code';
        $this->assertEntity($group->setCode($newCode));
        $this->assertEquals($newCode, $group->getCode());
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
     * Test getter/setter for attributes property
     */
    public function testAttributes()
    {
        $group = new AttributeGroup();
        $this->assertCount(0, $group->getAttributes());

        // Change value and assert new
        $newAttribute = new ProductAttribute();
        $this->assertEntity($group->addAttribute($newAttribute));
        $this->assertCount(1, $group->getAttributes());
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\ProductAttribute', $group->getAttributes()->first());
        $this->assertTrue($group->hasAttribute($newAttribute));

        $this->assertEntity($group->removeAttribute($newAttribute));
        $this->assertCount(0, $group->getAttributes());
    }

    /**
     * Test getter/setter for translations property
     */
    public function testTranslations()
    {
        $group = new AttributeGroup();
        $this->assertCount(0, $group->getTranslations());

        // Change value and assert new
        $newTranslation = new AttributeGroupTranslation();
        $this->assertEntity($group->addTranslation($newTranslation));
        $this->assertCount(1, $group->getTranslations());
        $this->assertInstanceOf(
            'Pim\Bundle\ProductBundle\Entity\AttributeGroupTranslation',
            $group->getTranslations()->first()
        );

        $group->addTranslation($newTranslation);
        $this->assertCount(1, $group->getTranslations());

        $this->assertEntity($group->removeTranslation($newTranslation));
        $this->assertCount(0, $group->getTranslations());
    }

    /**
     * Test related method
     * Just a call to prevent fatal errors (no way to verify value is set)
     */
    public function testSetLocale()
    {
        $group = new AttributeGroup();
        $group->setLocale('en_US');
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
