<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Entity;

use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\AssociationTranslation;

/**
 * Test related class
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Pim\Bundle\CatalogBundle\Entity\AssociationType
     */
    protected $associationType;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->associationType = new AssociationType();
    }

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $this->assertEntity($this->associationType);
        $this->assertInstanceOf(
            'Doctrine\Common\Collections\ArrayCollection',
            $this->associationType->getTranslations()
        );
        $this->assertCount(0, $this->associationType->getTranslations());
    }

    /**
     * Test getter/setter for id property
     */
    public function testId()
    {
        $this->assertEmpty($this->associationType->getId());
        $newId = 100;
        $this->associationType->setId($newId);
        $this->assertSame($newId, $this->associationType->getId());
    }

    /**
     * Test getter/setter for code property
     */
    public function testGetSetCode()
    {
        $this->assertEmpty($this->associationType->getCode());

        // Change value and assert new
        $newCode = 'test-code';
        $this->assertEntity($this->associationType->setCode($newCode));
        $this->assertEquals($newCode, $this->associationType->getCode());
    }

    /**
     * Test getter/setter for label property
     */
    public function testGetSetLabel()
    {
        // Change value and assert new
        $newCode = 'code';
        $expectedCode = '['. $newCode .']';
        $this->associationType->setCode($newCode);
        $this->assertEquals($expectedCode, $this->associationType->getLabel());

        $newLabel = 'test-label';
        $this->assertEntity($this->associationType->setLocale('en_US'));
        $this->assertEntity($this->associationType->setLabel($newLabel));
        $this->assertEquals($newLabel, $this->associationType->getLabel());

        // if no translation, assert the expected code is returned
        $this->associationType->setLocale('fr_FR');
        $this->assertEquals($expectedCode, $this->associationType->getLabel());

        // if empty translation, assert the expected code is returned
        $this->associationType->setLabel('');
        $this->assertEquals($expectedCode, $this->associationType->getLabel());
    }

    /**
     * Test for __toString method
     */
    public function testToString()
    {
        // Change value and assert new
        $newCode = 'toStringCode';
        $expectedCode = '['. $newCode .']';
        $this->associationType->setCode($newCode);
        $this->assertEquals($expectedCode, $this->associationType->__toString());

        $newLabel = 'toStringLabel';
        $this->assertEntity($this->associationType->setLocale('en_US'));
        $this->assertEntity($this->associationType->setLabel($newLabel));
        $this->assertEquals($newLabel, $this->associationType->__toString());

        // if no translation, assert the expected code is returned
        $this->associationType->setLocale('fr_FR');
        $this->assertEquals($expectedCode, $this->associationType->__toString());

        // if empty translation, assert the expected code is returned
        $this->associationType->setLabel('');
        $this->assertEquals($expectedCode, $this->associationType->__toString());
    }

    /**
     * Assert entity
     *
     * @param Pim\Bundle\CatalogBundle\Entity\AssociationType $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\AssociationType', $entity);
    }

    /**
     * Test getter/setter for created property
     */
    public function testCreated()
    {
        $this->assertNull($this->associationType->getCreated());

        // change value and assert new
        $newCreated = new \Datetime();
        $this->assertEntity($this->associationType->setCreated($newCreated));
        $this->assertEquals($newCreated, $this->associationType->getCreated());
    }

    /**
     * Test getter/setter for updated property
     */
    public function testUpdated()
    {
        $this->assertEmpty($this->associationType->getUpdated());

        // Change value and assert new
        $newUpdated = new \Datetime();
        $this->assertEntity($this->associationType->setUpdated($newUpdated));
        $this->assertEquals($newUpdated, $this->associationType->getUpdated());
    }

    /**
     * Test getter/setter for translations property
     */
    public function testTranslations()
    {
        $this->assertCount(0, $this->associationType->getTranslations());

        // Change value and assert new
        $newTranslation = new AssociationTranslation();
        $this->assertEntity($this->associationType->addTranslation($newTranslation));
        $this->assertCount(1, $this->associationType->getTranslations());
        $this->assertInstanceOf(
            'Pim\Bundle\CatalogBundle\Entity\AssociationTranslation',
            $this->associationType->getTranslations()->first()
        );

        $this->associationType->addTranslation($newTranslation);
        $this->assertCount(1, $this->associationType->getTranslations());

        $this->assertEntity($this->associationType->removeTranslation($newTranslation));
        $this->assertCount(0, $this->associationType->getTranslations());
    }
}
