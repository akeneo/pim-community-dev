<?php

namespace Pim\Bundle\ConfigBundle\Tests\Unit\Entity;

use Pim\Bundle\ConfigBundle\Entity\Currency;
use Pim\Bundle\ConfigBundle\Entity\Locale;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LocaleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test related method
     */
    public function testConstruct()
    {
        $locale = new Locale();
        $this->assertInstanceOf('Pim\Bundle\ConfigBundle\Entity\Locale', $locale);
    }

    /**
     * Test getter/setter for id property
     */
    public function testGetSetId()
    {
        $locale = new Locale();
        $this->assertEmpty($locale->getId());

        // change value and assert new
        $newId = 5;
        $locale->setId($newId);
        $this->assertEquals($newId, $locale->getId());
    }

    /**
     * Test getter/setter for code property
     */
    public function testGetSetCode()
    {
        $locale = new Locale();
        $this->assertEmpty($locale->getCode());

        // change value and assert new
        $newCode = 'fr_FR';
        $locale->setCode($newCode);
        $this->assertEquals($newCode, $locale->getCode());
    }

    /**
     * Test getter/setter for fallback property
     */
    public function testGetSetFallback()
    {
        $locale = new Locale();
        $this->assertEmpty($locale->getFallback());

        // change value and assert new
        $newFallback = 'fr_FR';
        $locale->setFallback($newFallback);
        $this->assertEquals($newFallback, $locale->getFallback());
    }

    /**
     * Test getter/setter for currencies property
     */
    public function testGetSetDefaultCurrency()
    {
        $locale = new Locale();

        $currencyCode = 'USD';
        $currencyUs = $this->createCurrency($currencyCode);
        $this->assertNull($locale->getDefaultCurrency());

        $locale->setDefaultCurrency($currencyUs);
        $this->assertInstanceOf('Pim\Bundle\ConfigBundle\Entity\Currency', $locale->getDefaultCurrency());
        $this->assertEquals($locale->getDefaultCurrency()->getCode(), $currencyCode);
    }

    /**
     * Create a currency for testing
     * @param string $code
     *
     * @return \Pim\Bundle\ConfigBundle\Entity\Currency
     */
    protected function createCurrency($code)
    {
        $currency = new Currency();
        $currency->setCode($code);

        return $currency;
    }

    /**
     * Test is/setter for activated property
     */
    public function testIsSetActivated()
    {
        $locale = new Locale();
        $this->assertTrue($locale->isActivated());

        // change value and assert new
        $newActivated = false;
        $locale->setActivated($newActivated);
        $this->assertFalse($locale->isActivated());
    }

    /**
     * Test for __toString method
     */
    public function testToString()
    {
        $locale = new Locale();
        $code = 'en_US';
        $locale->setCode($code);
        $this->assertEquals($code, $locale->__toString());
    }
}
