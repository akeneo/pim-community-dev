<?php

namespace Pim\Bundle\CatalogBundle\Tests\Functional\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CurrencyManagerTest extends WebTestCase
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var Pim\Bundle\CatalogBundle\Manager\CurrencyManager
     */
    protected $currencyManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        if (null !== static::$kernel) {
            static::$kernel->shutdown();
        }

        static::$kernel = static::createKernel(array("debug" => true));
        static::$kernel->boot();

        $this->container = static::$kernel->getContainer();

        $this->currencyManager = $this->container->get('pim_catalog.manager.currency');
    }

    /**
     * Test related method
     */
    public function testGetActiveCurrencies()
    {
        $currencies = $this->currencyManager->getActiveCurrencies();
        $expectedCurrencies = array('EUR', 'USD');

        $this->assertCount(2, $currencies);
        foreach ($currencies as $currency) {
            $this->assertContains($currency->getCode(), $expectedCurrencies);
        }
    }

    /**
     * Test related method
     */
    public function testGetActiveCodes()
    {
        $currencies = $this->currencyManager->getActiveCodes();
        $expectedCurrencies = array('EUR', 'USD');

        $this->assertCount(2, $currencies);
        foreach ($currencies as $currency) {
            $this->assertContains($currency, $expectedCurrencies);
        }
    }

    /**
     * Test related class
     */
    public function testGetCurrencies()
    {
        $currencies = $this->currencyManager->getCurrencies();
        $expectedCurrencies = array('EUR', 'USD');

        $this->assertGreaterThan(2, $currencies);
    }
}
