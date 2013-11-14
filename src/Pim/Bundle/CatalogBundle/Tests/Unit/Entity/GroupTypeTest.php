<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Entity;

use Pim\Bundle\CatalogBundle\Entity\GroupType;
use Pim\Bundle\CatalogBundle\Entity\GroupTypeTranslation;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Pim\Bundle\CatalogBundle\Entity\GroupType
     */
    protected $type;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->type = new GroupType();
    }

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $this->assertEntity($this->type);

        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $this->type->getGroups());
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $this->type->getTranslations());

        $this->assertCount(0, $this->type->getGroups());
        $this->assertCount(0, $this->type->getTranslations());
    }

    /**
     * Test getter for id property
     */
    public function testId()
    {
        $this->assertEmpty($this->type->getId());
    }

    /**
     * Test getter/setter for code property
     */
    public function testGetSetCode()
    {
        $this->assertEmpty($this->type->getCode());

        $newCode = 'test-code';
        $this->assertEntity($this->type->setCode($newCode));
        $this->assertEquals($newCode, $this->type->getCode());
    }

    /**
     * Test getter/setter for label property
     */
    public function testGetSetLabel()
    {
        $newCode = 'code';
        $expectedCode = '['. $newCode .']';
        $this->type->setCode($newCode);
        $this->assertEquals($expectedCode, $this->type->getLabel());

        $newLabel = 'test-label';
        $this->assertEntity($this->type->setLocale('en_US'));
        $this->assertEntity($this->type->setLabel($newLabel));
        $this->assertEquals($newLabel, $this->type->getLabel());

        $this->type->setLocale('fr_FR');
        $this->assertEquals($expectedCode, $this->type->getLabel());

        $this->type->setLabel('');
        $this->assertEquals($expectedCode, $this->type->getLabel());
    }

    /**
     * Test for __toString method
     */
    public function testToString()
    {
        $newCode = 'toStringCode';
        $expectedCode = '['. $newCode .']';
        $this->type->setCode($newCode);
        $this->assertEquals($expectedCode, $this->type->__toString());

        $newLabel = 'toStringLabel';
        $this->assertEntity($this->type->setLocale('en_US'));
        $this->assertEntity($this->type->setLabel($newLabel));
        $this->assertEquals($newLabel, $this->type->__toString());

        $this->type->setLocale('fr_FR');
        $this->assertEquals($expectedCode, $this->type->__toString());

        $this->type->setLabel('');
        $this->assertEquals($expectedCode, $this->type->__toString());
    }

    /**
     * Test getter/setter for translations property
     */
    public function testTranslations()
    {
        $this->assertCount(0, $this->type->getTranslations());

        // Change value and assert new
        $newTranslation = new GroupTypeTranslation();
        $this->assertEntity($this->type->addTranslation($newTranslation));
        $this->assertCount(1, $this->type->getTranslations());
        $this->assertInstanceOf(
            'Pim\Bundle\CatalogBundle\Entity\GroupTypeTranslation',
            $this->type->getTranslations()->first()
        );

        $this->type->addTranslation($newTranslation);
        $this->assertCount(1, $this->type->getTranslations());

        $this->assertEntity($this->type->removeTranslation($newTranslation));
        $this->assertCount(0, $this->type->getTranslations());
    }

    /**
     * Assert entity
     *
     * @param Pim\Bundle\CatalogBundle\Entity\GroupType $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\GroupType', $entity);
    }
}
