<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Entity;

use Pim\Bundle\CatalogBundle\Entity\Locale;
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
     * Test getter/setter for locales property
     */
    public function testGetSetLocales()
    {
        $this->assertInstanceOf('\Doctrine\Common\Collections\ArrayCollection', $this->currency->getLocales());
        $this->assertCount(0, $this->currency->getLocales());

        // create locales
        $langFr = $this->createLocale('fr_FR', 'fr_FR');
        $langUs = $this->createLocale('en_US', 'en_EN');
        $langEn = $this->createLocale('en_EN', 'en_EN');

        // Set locales and assert
        $newLocales = [$langFr, $langUs, $langEn];
        $this->assertEntity($this->currency->setLocales([$langFr, $langUs, $langEn]));
        $this->assertInstanceOf('\Doctrine\Common\Collections\ArrayCollection', $this->currency->getLocales());
        $this->assertCount(3, $this->currency->getLocales());
        foreach ($this->currency->getLocales() as $locale) {
            $this->assertTrue(in_array($locale, $newLocales));
        }
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
     * Create a locale for testing
     * @param string $code     Locale code
     * @param string $fallback Fallback code
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Locale
     */
    protected function createLocale($code, $fallback)
    {
        $locale = new Locale();
        $locale->setCode($code);
        $locale->setFallback($fallback);

        return $locale;
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
