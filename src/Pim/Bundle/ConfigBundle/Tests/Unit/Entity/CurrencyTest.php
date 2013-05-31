<?php

namespace Pim\Bundle\ConfigBundle\Tests\Unit\Entity;

use Pim\Bundle\ConfigBundle\Entity\Locale;
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

    public function testToggleActivation()
    {
        $currency = new Currency();
        $currency->toggleActivation();
        $this->assertFalse($currency->getActivated());

        $currency->toggleActivation();
        $this->assertTrue($currency->getActivated());
    }

    /**
     * Test getter/setter for locales property
     */
    public function testGetSetLocales()
    {
        $currency = new Currency();
        $this->assertInstanceOf('\Doctrine\Common\Collections\ArrayCollection', $currency->getLocales());
        $this->assertCount(0, $currency->getLocales());

        // create locales
        $listLocales = array('fr_FR', 'en_US', 'en_EN');

        $langFr = $this->createLocale('fr_FR', 'fr_FR');
        $langUs = $this->createLocale('en_US', 'en_EN');
        $langEn = $this->createLocale('en_EN', 'en_EN');

        // Set locales and assert
        $newLocales = array($langFr, $langUs, $langEn);
        $currency->setLocales(array($langFr, $langUs, $langEn));
        $this->assertInstanceOf('\Doctrine\Common\Collections\ArrayCollection', $currency->getLocales());
        $this->assertCount(3, $currency->getLocales());
        foreach ($currency->getLocales() as $locale) {
            $this->assertTrue(in_array($locale, $newLocales));
        }
    }

    /**
     * Create a locale for testing
     * @param string $code     Locale code
     * @param string $fallback Fallback code
     *
     * @return \Pim\Bundle\ConfigBundle\Entity\Locale
     */
    protected function createLocale($code, $fallback)
    {
        $locale = new Locale();
        $locale->setCode($code);
        $locale->setFallback($fallback);

        return $locale;
    }
}
