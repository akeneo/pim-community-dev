<?php
namespace Pim\Bundle\ConfigBundle\Tests\Unit\Entity;

use Pim\Bundle\ConfigBundle\Entity\Currency;

use Pim\Bundle\ConfigBundle\Entity\Locale;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
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
    public function testGetSetCurrencies()
    {
        $locale = new Locale();
        $this->assertInstanceOf('\Doctrine\Common\Collections\ArrayCollection', $locale->getCurrencies());
        $this->assertCount(0, $locale->getCurrencies());

        // create currencies
        $listCurrencies = array('USD', 'EUR', 'GPB');

        $currencyUs = $this->createCurrency('USD');
        $currencyFr = $this->createCurrency('EUR');
        $currencyEn = $this->createCurrency('GPB');

        // Set currencies and assert
        $newCurrencies = array($currencyUs, $currencyFr);
        $locale->setCurrencies($newCurrencies);
        $this->assertInstanceOf('\Doctrine\Common\Collections\ArrayCollection', $locale->getCurrencies());
        $this->assertCount(2, $locale->getCurrencies());
        foreach ($locale->getCurrencies() as $currency) {
            $this->assertTrue(in_array($currency, $newCurrencies));
        }

        // Add currency and assert
        $locale->addCurrency($currencyEn);
        $this->assertInstanceOf('\Doctrine\Common\Collections\ArrayCollection', $locale->getCurrencies());
        $this->assertCount(3, $locale->getCurrencies());
        foreach ($locale->getCurrencies() as $currency) {
            $this->assertTrue(in_array($currency, array($currencyUs, $currencyFr, $currencyEn)));
        }

        // Remove currency and assert
        $locale->removeCurrency($currencyFr);
        $this->assertInstanceOf('\Doctrine\Common\Collections\ArrayCollection', $locale->getCurrencies());
        $this->assertCount(2, $locale->getCurrencies());
        foreach ($locale->getCurrencies() as $currency) {
            $this->assertTrue(in_array($currency, array($currencyUs, $currencyEn)));
        }
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
     * Test getter/setter for activated property
     */
    public function testGetSetActivated()
    {
        $locale = new Locale();
        $this->assertTrue($locale->getActivated());

        // change value and assert new
        $newActivated = false;
        $locale->setActivated($newActivated);
        $this->assertFalse($locale->getActivated());
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
