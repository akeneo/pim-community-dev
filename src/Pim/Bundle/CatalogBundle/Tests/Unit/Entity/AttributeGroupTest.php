<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Entity;

use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroupTranslation;
use Pim\Bundle\CatalogBundle\Entity\Attribute;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributeGroup
     */
    protected $group;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->group = new AttributeGroup();
    }

    /**
     * Test related method
     */
    public function testConstruct()
    {
        // assert instance and implementation
        $this->assertEntity($this->group);
        $this->assertInstanceOf('\Pim\Bundle\TranslationBundle\Entity\TranslatableInterface', $this->group);

        // assert object properties
        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $this->group->getAttributes());
        $this->assertInstanceOf('\Doctrine\Common\Collections\Collection', $this->group->getTranslations());
        $this->assertCount(0, $this->group->getAttributes());
        $this->assertCount(0, $this->group->getTranslations());
    }

    /**
     * Test getter/setter for id property
     */
    public function testId()
    {
        $this->assertEmpty($this->group->getId());

        // Change value and assert new
        $newId = 7;
        $this->assertEntity($this->group->setId($newId));
        $this->assertEquals($newId, $this->group->getId());
    }

    /**
     * Test getter/setter for code property
     */
    public function testCode()
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
    public function testLabel()
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

        // if default code, assert the code is returned
        $this->group->setLocale('en_US');
        $this->group->setCode(AttributeGroup::DEFAULT_GROUP_CODE);
        $this->assertEquals(AttributeGroup::DEFAULT_GROUP_CODE, $this->group->getLabel());
    }

    /**
     * Test for _toString method
     */
    public function testToString()
    {
        // Change value and assert new
        $newCode = 'code';
        $expectedCode = '['. $newCode .']';
        $this->group->setCode($newCode);
        $this->assertEquals($expectedCode, $this->group->__toString());

        $newLabel = 'test-label';
        $this->assertEntity($this->group->setLocale('en_US'));
        $this->assertEntity($this->group->setLabel($newLabel));
        $this->assertEquals($newLabel, $this->group->__toString());

        // if no translation, assert the expected code is returned
        $this->group->setLocale('fr_FR');
        $this->assertEquals($expectedCode, $this->group->__toString());

        // if empty translation, assert the expected code is returned
        $this->group->setLabel('');
        $this->assertEquals($expectedCode, $this->group->__toString());

        // if default code, assert the code is returned
        $this->group->setLocale('en_US');
        $this->group->setCode(AttributeGroup::DEFAULT_GROUP_CODE);
        $this->assertEquals(AttributeGroup::DEFAULT_GROUP_CODE, $this->group->__toString());
    }

    /**
     * Test getter/setter for sort order property
     */
    public function testSortOrder()
    {
        $this->assertEmpty($this->group->getSortOrder());

        // Change value and assert new
        $newOrder = 5;
        $this->assertEntity($this->group->setSortOrder($newOrder));
        $this->assertEquals($newOrder, $this->group->getSortOrder());
    }

    /**
     * Test getter/setter for created property
     */
    public function testCreated()
    {
        $this->assertEmpty($this->group->getCreated());

        // Change value and assert new
        $newCreated = new \Datetime();
        $this->assertEntity($this->group->setCreated($newCreated));
        $this->assertEquals($newCreated, $this->group->getCreated());
    }

    /**
     * Test getter/setter for updated property
     */
    public function testUpdated()
    {
        $this->assertEmpty($this->group->getUpdated());

        // Change value and assert new
        $newUpdated = new \Datetime();
        $this->assertEntity($this->group->setUpdated($newUpdated));
        $this->assertEquals($newUpdated, $this->group->getUpdated());
    }

    /**
     * Test getter/setter for attributes property
     */
    public function testAttributes()
    {
        $this->assertCount(0, $this->group->getAttributes());

        // Change value and assert new
        $newAttribute = new Attribute();
        $this->assertEntity($this->group->addAttribute($newAttribute));
        $this->assertCount(1, $this->group->getAttributes());
        $this->assertInstanceOf(
            'Pim\Bundle\CatalogBundle\Entity\Attribute',
            $this->group->getAttributes()->first()
        );
        $this->assertTrue($this->group->hasAttribute($newAttribute));

        $this->assertEntity($this->group->removeAttribute($newAttribute));
        $this->assertCount(0, $this->group->getAttributes());
    }

    /**
     * Test getter for max attribute sort order
     */
    public function testGetMaxAttributeSortOrder()
    {
        $max = 5;
        for ($i = 1; $i <= $max; $i++) {
            $attribute = new Attribute();
            $attribute->setSortOrder($i);
            $this->group->addAttribute($attribute);
        }

        $this->assertEquals($this->group->getMaxAttributeSortOrder(), $max);
    }

    /**
     * Test getter/setter for translations property
     */
    public function testTranslations()
    {
        $this->assertCount(0, $this->group->getTranslations());

        // Change value and assert new
        $newTranslation = new AttributeGroupTranslation();
        $this->assertEntity($this->group->addTranslation($newTranslation));
        $this->assertCount(1, $this->group->getTranslations());
        $this->assertInstanceOf(
            'Pim\Bundle\CatalogBundle\Entity\AttributeGroupTranslation',
            $this->group->getTranslations()->first()
        );

        $this->group->addTranslation($newTranslation);
        $this->assertCount(1, $this->group->getTranslations());

        $this->assertEntity($this->group->removeTranslation($newTranslation));
        $this->assertCount(0, $this->group->getTranslations());
    }

    /**
     * Test related method
     * Just a call to prevent fatal errors (no way to verify value is set)
     */
    public function testSetLocale()
    {
        $this->group->setLocale('en_US');
    }

    /**
     * Assert entity
     * @param Pim\Bundle\CatalogBundle\Entity\AttributeGroup $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\AttributeGroup', $entity);
    }
}
