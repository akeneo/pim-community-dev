<?php
namespace Pim\Bundle\ConfigBundle\Tests\Unit\Entity;

use Pim\Bundle\ConfigBundle\Entity\Currency;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CurrencyTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $currency = new Currency();
        $this->assertInstanceOf('Pim\Bundle\ConfigBundle\Entity\Currency', $currency);
    }

    /**
     * Test getter/setter for id property
     */
    public function testGetSetId()
    {
        $currency = new Currency();
        $this->assertEmpty($currency->getId());

        // change value and assert new
        $newId = 5;
        $currency->setId($newId);
        $this->assertEquals($newId, $currency->getId());
    }

    /**
     * Test getter/setter for code property
     */
    public function testGetSetCode()
    {
        $currency = new Currency();
        $this->assertEmpty($currency->getCode());

        // change value and assert new
        $newCode = 'EUR';
        $currency->setCode($newCode);
        $this->assertEquals($newCode, $currency->getCode());
    }

    /**
     * Test getter/setter for label property
     */
    public function testGetSetLabel()
    {
        $currency = new Currency();
        $this->assertEmpty($currency->getLabel());

        // change value and assert new
        $newLabel = 'Euro';
        $currency->setLabel($newLabel);
        $this->assertEquals($newLabel, $currency->getLabel());
    }

    /**
     * Test getter/setter for activated property
     */
    public function testGetSetActivated()
    {
        $currency = new Currency();
        $this->assertTrue($currency->getActivated());

        // change value and assert new
        $newActivated = false;
        $currency->setActivated($newActivated);
        $this->assertFalse($currency->getActivated());
    }
}
