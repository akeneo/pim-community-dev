<?php

namespace Pim\Bundle\CatalogBundle\Tests\Functional\Manager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleManagerTest extends WebTestCase
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var Pim\Bundle\CatalogBundle\Manager\LocaleManager
     */
    protected $localeManager;

    /**
     * List of activated locales
     * @staticvar $activatedLocales
     */
    protected static $activatedLocales = array('en_US', 'fr_FR');

    /**
     * Count of all locales
     * @staticvar integer
     */
    protected static $totalCount = 82;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->markTestSkipped('Due to locale refactoring PIM-861, to replace by behat scenario');

        if (null !== static::$kernel) {
            static::$kernel->shutdown();
        }

        static::$kernel = static::createKernel(array("debug" => false));
        static::$kernel->boot();

        $this->container = static::$kernel->getContainer();

        $this->localeManager = $this->container->get('pim_catalog.manager.locale');
    }

    /**
     * Test related method
     */
    public function testGetActiveLocales()
    {
        $locales = $this->localeManager->getActiveLocales();
        $expectedLocales = static::$activatedLocales;

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
        $unexpectedLocales = static::$activatedLocales;
        $expectedCount = self::$totalCount - count(static::$activatedLocales);

        $this->assertCount($expectedCount, $locales);
        foreach ($locales as $locale) {
            $this->assertNotContains($locale->getCode(), $unexpectedLocales);
        }
    }

    /**
     * Test related method
     */
    public function testGetActiveCodes()
    {
        $locales = $this->localeManager->getActiveCodes();
        $expectedLocales = static::$activatedLocales;

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
        $expectedLocales = static::$activatedLocales;

        $this->assertCount(self::$totalCount, $locales);
        foreach ($locales as $locale) {
            $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\Locale', $locale);
        }
    }
}
