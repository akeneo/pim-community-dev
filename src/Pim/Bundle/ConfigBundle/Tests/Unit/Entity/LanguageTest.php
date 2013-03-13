<?php
namespace Pim\Bundle\ConfigBundle\Tests\Unit\Entity;

use Pim\Bundle\ConfigBundle\Entity\Currency;

use Pim\Bundle\ConfigBundle\Entity\Language;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LanguageTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $language = new Language();
        $this->assertInstanceOf('Pim\Bundle\ConfigBundle\Entity\Language', $language);
    }

    /**
     * Test getter/setter for id property
     */
    public function testGetSetId()
    {
        $language = new Language();
        $this->assertEmpty($language->getId());

        // change value and assert new
        $newId = 5;
        $language->setId($newId);
        $this->assertEquals($newId, $language->getId());
    }

    /**
     * Test getter/setter for code property
     */
    public function testGetSetCode()
    {
        $language = new Language();
        $this->assertEmpty($language->getCode());

        // change value and assert new
        $newCode = 'fr_FR';
        $language->setCode($newCode);
        $this->assertEquals($newCode, $language->getCode());
    }

    /**
     * Test getter/setter for fallback property
     */
    public function testGetSetFallback()
    {
        $language = new Language();
        $this->assertEmpty($language->getFallback());

        // change value and assert new
        $newFallback = 'fr_FR';
        $language->setFallback($newFallback);
        $this->assertEquals($newFallback, $language->getFallback());
    }

    /**
     * Test getter/setter for currencies property
     */
    public function testGetSetCurrencies()
    {
        $language = new Language();
        $this->assertInstanceOf('\Doctrine\Common\Collections\ArrayCollection', $language->getCurrencies());
        $this->assertCount(0, $language->getCurrencies());

        // create currencies
        $listCurrencies = array('USD', 'EUR', 'GPB');

        $currencyUs = $this->createCurrency('USD');
        $currencyFr = $this->createCurrency('EUR');
        $currencyEn = $this->createCurrency('GPB');

        // Set currencies and assert
        $newCurrencies = array($currencyUs, $currencyFr);
        $language->setCurrencies($newCurrencies);
        $this->assertInstanceOf('\Doctrine\Common\Collections\ArrayCollection', $language->getCurrencies());
        $this->assertCount(2, $language->getCurrencies());
        foreach ($language->getCurrencies() as $currency) {
            $this->assertTrue(in_array($currency, $newCurrencies));
        }

        // Add currency and assert
        $language->addCurrency($currencyEn);
        $this->assertInstanceOf('\Doctrine\Common\Collections\ArrayCollection', $language->getCurrencies());
        $this->assertCount(3, $language->getCurrencies());
        foreach ($language->getCurrencies() as $currency) {
            $this->assertTrue(in_array($currency, array($currencyUs, $currencyFr, $currencyEn)));
        }

        // Remove currency and assert
        $language->removeCurrency($currencyFr);
        $this->assertInstanceOf('\Doctrine\Common\Collections\ArrayCollection', $language->getCurrencies());
        $this->assertCount(2, $language->getCurrencies());
        foreach ($language->getCurrencies() as $currency) {
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
        $language = new Language();
        $this->assertTrue($language->getActivated());

        // change value and assert new
        $newActivated = false;
        $language->setActivated($newActivated);
        $this->assertFalse($language->getActivated());
    }

    /**
     * Test for __toString method
     */
    public function testToString()
    {
        $language = new Language();
        $code = 'en_US';
        $language->setCode($code);
        $this->assertEquals($code, $language->__toString());
    }
}
