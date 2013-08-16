<?php

namespace Pim\Bundle\ProductBundle\Tests\Functional\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LocaleManagerTest extends WebTestCase
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var Pim\Bundle\ProductBundle\Manager\LocaleManager
     */
    protected $localeManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->markTestSkipped('Due to locale refactoring PIM-861, to replace by behat scenario');

        if (null !== static::$kernel) {
            static::$kernel->shutdown();
        }

        static::$kernel = static::createKernel(array("debug" => true));
        static::$kernel->boot();

        $this->container = static::$kernel->getContainer();

        $this->localeManager = $this->container->get('pim_product.manager.locale');
    }

    /**
     * Test related method
     */
    public function testGetActiveLocales()
    {
        $locales = $this->localeManager->getActiveLocales();
        $expectedLocales = array('en_US', 'fr_FR', 'de_DE');

        $this->assertCount(count($expectedLocales), $locales);
        foreach ($locales as $locale) {
            $this->assertContains($locale->getCode(), $expectedLocales);
        }
    }

    /**
     * Test related method
     */
    public function testGetDisabledLocales()
    {
        $locales = $this->localeManager->getDisabledLocales();
        $expectedLocales = array();

        $this->assertCount(0, $locales);
        foreach ($locales as $locale) {
            $this->assertContains($locale->getCode(), $expectedLocales);
        }
    }

    /**
     * Test related method
     */
    public function testGetActiveCodes()
    {
        $locales = $this->localeManager->getActiveCodes();
        $expectedLocales = array('en_US', 'fr_FR', 'de_DE');

        $this->assertCount(count($expectedLocales), $locales);
        foreach ($locales as $locale) {
            $this->assertContains($locale, $expectedLocales);
        }
    }

    /**
     * Test related class
     */
    public function testGetLocales()
    {
        $locales = $this->localeManager->getLocales();
        $expectedLocales = array('en_US', 'fr_FR', 'de_DE');

        $this->assertCount(count($expectedLocales), $locales);
        foreach ($locales as $locale) {
            $this->assertContains($locale->getCode(), $expectedLocales);
        }
    }
}
