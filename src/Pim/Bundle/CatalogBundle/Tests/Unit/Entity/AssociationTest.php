<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Entity;

use Pim\Bundle\CatalogBundle\Entity\Association;
use Pim\Bundle\CatalogBundle\Entity\AssociationTranslation;

/**
 * Test related class
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Pim\Bundle\CatalogBundle\Entity\Association
     */
    protected $association;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->association = new Association();
    }

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $this->assertEntity($this->association);
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $this->association->getTranslations());
        $this->assertCount(0, $this->association->getTranslations());
    }

    /**
     * Test getter/setter for id property
     */
    public function testId()
    {
        $this->assertEmpty($this->association->getId());
        $newId = 100;
        $this->association->setId($newId);
        $this->assertSame($newId, $this->association->getId());
    }

    /**
     * Test getter/setter for code property
     */
    public function testGetSetCode()
    {
        $this->assertEmpty($this->association->getCode());

        // Change value and assert new
        $newCode = 'test-code';
        $this->assertEntity($this->association->setCode($newCode));
        $this->assertEquals($newCode, $this->association->getCode());
    }

    /**
     * Test getter/setter for label property
     */
    public function testGetSetLabel()
    {
        // Change value and assert new
        $newCode = 'code';
        $expectedCode = '['. $newCode .']';
        $this->association->setCode($newCode);
        $this->assertEquals($expectedCode, $this->association->getLabel());

        $newLabel = 'test-label';
        $this->assertEntity($this->association->setLocale('en_US'));
        $this->assertEntity($this->association->setLabel($newLabel));
        $this->assertEquals($newLabel, $this->association->getLabel());

        // if no translation, assert the expected code is returned
        $this->association->setLocale('fr_FR');
        $this->assertEquals($expectedCode, $this->association->getLabel());

        // if empty translation, assert the expected code is returned
        $this->association->setLabel('');
        $this->assertEquals($expectedCode, $this->association->getLabel());
    }

    /**
     * Test for __toString method
     */
    public function testToString()
    {
        // Change value and assert new
        $newCode = 'toStringCode';
        $expectedCode = '['. $newCode .']';
        $this->association->setCode($newCode);
        $this->assertEquals($expectedCode, $this->association->__toString());

        $newLabel = 'toStringLabel';
        $this->assertEntity($this->association->setLocale('en_US'));
        $this->assertEntity($this->association->setLabel($newLabel));
        $this->assertEquals($newLabel, $this->association->__toString());

        // if no translation, assert the expected code is returned
        $this->association->setLocale('fr_FR');
        $this->assertEquals($expectedCode, $this->association->__toString());

        // if empty translation, assert the expected code is returned
        $this->association->setLabel('');
        $this->assertEquals($expectedCode, $this->association->__toString());
    }

    /**
     * Assert entity
     *
     * @param Pim\Bundle\CatalogBundle\Entity\Association $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\Association', $entity);
    }

    /**
     * Test getter/setter for created property
     */
    public function testCreated()
    {
        $this->assertNull($this->association->getCreated());

        // change value and assert new
        $newCreated = new \Datetime();
        $this->assertEntity($this->association->setCreated($newCreated));
        $this->assertEquals($newCreated, $this->association->getCreated());
    }

    /**
     * Test getter/setter for updated property
     */
    public function testUpdated()
    {
        $this->assertEmpty($this->association->getUpdated());

        // Change value and assert new
        $newUpdated = new \Datetime();
        $this->assertEntity($this->association->setUpdated($newUpdated));
        $this->assertEquals($newUpdated, $this->association->getUpdated());
    }

    /**
     * Test getter/setter for translations property
     */
    public function testTranslations()
    {
        $this->assertCount(0, $this->association->getTranslations());

        // Change value and assert new
        $newTranslation = new AssociationTranslation();
        $this->assertEntity($this->association->addTranslation($newTranslation));
        $this->assertCount(1, $this->association->getTranslations());
        $this->assertInstanceOf(
            'Pim\Bundle\CatalogBundle\Entity\AssociationTranslation',
            $this->association->getTranslations()->first()
        );

        $this->association->addTranslation($newTranslation);
        $this->assertCount(1, $this->association->getTranslations());

        $this->assertEntity($this->association->removeTranslation($newTranslation));
        $this->assertCount(0, $this->association->getTranslations());
    }
}
