<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Entity;

use Pim\Bundle\CatalogBundle\Model\Category;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Currency;
use Pim\Bundle\CatalogBundle\Entity\Channel;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Channel
     */
    protected $channel;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->channel = new Channel();
    }

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $this->assertEntity($this->channel);
    }

    /**
     * Test getter/setter for id property
     */
    public function testGetSetId()
    {
        $this->assertEmpty($this->channel->getId());

        // change value and assert new
        $newId = 5;
        $this->assertEntity($this->channel->setId($newId));
        $this->assertEquals($newId, $this->channel->getId());
    }

    /**
     * Test getter/setter for code property
     */
    public function testGetSetCode()
    {
        $this->assertEmpty($this->channel->getCode());

        // change value and assert new
        $newCode = 'ecommerce';
        $this->assertEntity($this->channel->setCode($newCode));
        $this->assertEquals($newCode, $this->channel->getCode());
    }

    /**
     * Test getter/setter for label property
     */
    public function testGetSetLabel()
    {
        $this->assertEmpty($this->channel->getLabel());

        // change value and assert new
        $newLabel = 'E-Commerce';
        $this->assertEntity($this->channel->setLabel($newLabel));
        $this->assertEquals($newLabel, $this->channel->getLabel());
    }

    /**
     * Test getter/setter for category property
     */
    public function testGetSetCategory()
    {
        $this->assertNull($this->channel->getCategory());

        $expectedCategory = $this->createCategory('test-tree');
        $this->assertEntity($this->channel->setCategory($expectedCategory));
        $this->assertEquals($expectedCategory, $this->channel->getCategory());
    }

    /**
     * Create a category for testing
     *
     * @param string $code
     *
     * @return \Pim\Bundle\CatalogBundle\Model\Category
     */
    protected function createCategory($code)
    {
        $category = new Category();
        $category->setCode($code);

        return $category;
    }

    /**
     * Test getter/add/remove for currency property
     */
    public function testGetAddRemoveCurrency()
    {
        $this->assertCount(0, $this->channel->getCurrencies());

        // assert adding the right entity
        $expectedCurrencyEUR = $this->createCurrency('EUR');
        $this->assertEntity($this->channel->addCurrency($expectedCurrencyEUR));
        $this->assertCount(1, $this->channel->getCurrencies());

        $currency = $this->channel->getCurrencies()->first();
        $this->assertEquals($expectedCurrencyEUR, $currency);

        // assert removing the right entity
        $expectedCurrencyUSD = $this->createCurrency('USD');
        $this->channel->addCurrency($expectedCurrencyUSD);
        $this->assertCount(2, $this->channel->getCurrencies());

        $this->assertEntity($this->channel->removeCurrency($expectedCurrencyEUR));
        $this->assertCount(1, $this->channel->getCurrencies());
        $currency = $this->channel->getCurrencies()->first();
        $this->assertEquals($expectedCurrencyUSD, $currency);
    }

    /**
     * Create a currency for testing
     *
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
     * Test getter/add/remove/has for locale property
     */
    public function testGetAddRemoveHasLocale()
    {
        $this->assertCount(0, $this->channel->getLocales());

        // assert adding the right entity
        $expectedLocaleFR = $this->createLocale('fr_FR');
        $this->assertEntity($this->channel->addLocale($expectedLocaleFR));
        $this->assertCount(1, $this->channel->getLocales());

        $locale = $this->channel->getLocales()->first();
        $this->assertEquals($expectedLocaleFR, $locale);

        // assert removing the right entity
        $expectedLocaleEN = $this->createLocale('en_US');
        $this->channel->addLocale($expectedLocaleEN);
        $this->assertCount(2, $this->channel->getLocales());

        $this->assertEntity($this->channel->removeLocale($expectedLocaleFR));
        $this->assertCount(1, $this->channel->getLocales());
        $locale = $this->channel->getLocales()->first();
        $this->assertEquals($expectedLocaleEN, $locale);

        // assert add an already defined locale
        $this->channel->addLocale($expectedLocaleEN);
        $this->assertCount(1, $this->channel->getLocales());

        // assert if a channel has a locale
        $this->assertTrue($this->channel->hasLocale($expectedLocaleEN));
        $this->assertFalse($this->channel->hasLocale($expectedLocaleFR));
    }

    /**
     * Test related method
     */
    public function testPreRemove()
    {
        $locale1 = $this->createLocale('en_US');
        $locale1->activate();
        $locale2 = $this->createLocale('fr_FR');
        $locale2->activate();

        $this->assertTrue($locale1->isActivated());
        $this->assertTrue($locale2->isActivated());

        $this->channel->addLocale($locale1);
        $this->channel->addLocale($locale2);

        $this->channel->preRemove();

        $this->assertFalse($locale1->isActivated());
        $this->assertFalse($locale2->isActivated());
    }

    /**
     * Create a locale for testing
     *
     * @param string $code
     *
     * @return \Pim\Bundle\CatalogBundle\Tests\Unit\Entity\Locale
     */
    protected function createLocale($code)
    {
        $locale = new Locale();
        $locale->setCode($code);

        return $locale;
    }

    /**
     * Assert an entity
     *
     * @param Channel $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\Channel', $entity);
    }
}
