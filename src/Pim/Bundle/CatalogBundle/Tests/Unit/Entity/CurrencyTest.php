<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Entity;

use Pim\Bundle\CatalogBundle\Entity\Currency;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CurrencyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Currency
     */
    protected $currency;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->currency = new Currency();
    }

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $this->assertEntity($this->currency);
    }

    /**
     * Test getter/setter for id property
     */
    public function testGetSetId()
    {
        $this->assertEmpty($this->currency->getId());

        // change value and assert new
        $newId = 5;
        $this->assertEntity($this->currency->setId($newId));
        $this->assertEquals($newId, $this->currency->getId());
    }

    /**
     * Test getter/setter for code property
     */
    public function testGetSetCode()
    {
        $this->assertEmpty($this->currency->getCode());

        // change value and assert new
        $newCode = 'EUR';
        $this->assertEntity($this->currency->setCode($newCode));
        $this->assertEquals($newCode, $this->currency->getCode());
    }

    /**
     * Test getter/setter for activated property
     */
    public function testIsSetActivated()
    {
        $this->assertTrue($this->currency->isActivated());

        // change value and assert new
        $newActivated = false;
        $this->assertEntity($this->currency->setActivated($newActivated));
        $this->assertFalse($this->currency->isActivated());

        // change value and assert new
        $newActivated = true;
        $this->assertEntity($this->currency->setActivated($newActivated));
        $this->assertTrue($this->currency->isActivated());
    }

    /**
     * Test related method
     */
    public function testToggleActivation()
    {
        $this->currency->toggleActivation();
        $this->assertFalse($this->currency->isActivated());

        $this->currency->toggleActivation();
        $this->assertTrue($this->currency->isActivated());
    }

    /**
     * Test related method
     */
    public function testToString()
    {
        $this->assertEquals('', $this->currency->__toString());

        $expectedCode = 'test-code';
        $this->currency->setCode($expectedCode);
        $this->assertEquals($expectedCode, $this->currency->__toString());
    }

    /**
     * Assert an entity
     *
     * @param Currency $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\Currency', $entity);
    }
}
