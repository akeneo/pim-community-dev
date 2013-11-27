<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Entity;

use Pim\Bundle\CatalogBundle\Entity\Currency;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Channel;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Locale
     */
    protected $locale;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->locale = new Locale();
    }

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $this->assertEntity($this->locale);
        $this->assertFalse($this->locale->isActivated());
    }

    /**
     * Test getter/setter for id property
     */
    public function testGetSetId()
    {
        $this->assertEmpty($this->locale->getId());

        // change value and assert new
        $newId = 5;
        $this->assertEntity($this->locale->setId($newId));
        $this->assertEquals($newId, $this->locale->getId());
    }

    /**
     * Test getter/setter for code property
     */
    public function testGetSetCode()
    {
        $this->assertEmpty($this->locale->getCode());

        // change value and assert new
        $newCode = 'fr_FR';
        $this->assertEntity($this->locale->setCode($newCode));
        $this->assertEquals($newCode, $this->locale->getCode());
    }

    /**
     * Test getter/setter for fallback property
     */
    public function testGetSetFallback()
    {
        $this->assertEmpty($this->locale->getFallback());

        // change value and assert new
        $newFallback = 'fr_FR';
        $this->assertEntity($this->locale->setFallback($newFallback));
        $this->assertEquals($newFallback, $this->locale->getFallback());
    }

    /**
     * Test getter/setter for currencies property
     */
    public function testGetSetDefaultCurrency()
    {
        $currencyCode = 'USD';
        $currencyUs = $this->createCurrency($currencyCode);
        $this->assertNull($this->locale->getDefaultCurrency());

        $this->assertEntity($this->locale->setDefaultCurrency($currencyUs));
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\Currency', $this->locale->getDefaultCurrency());
        $this->assertEquals($this->locale->getDefaultCurrency()->getCode(), $currencyCode);
    }

    /**
     * Create a currency for testing
     * @param string $code
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Currency
     */
    protected function createCurrency($code)
    {
        $currency = new Currency();
        $currency->setCode($code);

        return $currency;
    }

    /**
     * Test for __toString method
     */
    public function testToString()
    {
        $code = 'en_US';
        $this->locale->setCode($code);
        $this->assertEquals($code, $this->locale->__toString());
    }

    /**
     * Test activate/deactivate locale and chech isActivated method
     */
    public function testActivateDeactivate()
    {
        $this->assertFalse($this->locale->isActivated());

        $channel = new Channel();

        $this->assertEntity($this->locale->addChannel($channel));
        $this->assertTrue($this->locale->isActivated());

        $this->assertEntity($this->locale->removeChannel($channel));
        $this->assertFalse($this->locale->isActivated());
    }

    /**
     * Assert an entity
     *
     * @param Locale $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\Locale', $entity);
    }
}
