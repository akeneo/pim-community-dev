<?php
namespace Pim\Bundle\ConfigBundle\Tests\Functional\Manager;

use Pim\Bundle\ConfigBundle\Manager\LocaleManager;

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
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var LocaleManager
     */
    protected $localeManager;

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

        $this->em = $this->container->get('doctrine.orm.entity_manager');

        $this->localeManager = new LocaleManager($this->em);
    }

    /**
     * Test related method
     */
    public function testGetActiveLocales()
    {
        $locales = $this->localeManager->getActiveLocales();
        $expectedLocales = array('en_US', 'fr_FR', 'en_GB');

        $this->assertCount(3, $locales);
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
        $expectedLocales = array('fr_CA');

        $this->assertCount(1, $locales);
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
        $expectedLocales = array('en_US', 'fr_FR', 'en_GB');

        $this->assertCount(3, $locales);
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
        $expectedLocales = array('en_US', 'fr_FR', 'en_GB', 'fr_CA');

        $this->assertCount(4, $locales);
        foreach ($locales as $locale) {
            $this->assertContains($locale->getCode(), $expectedLocales);
        }
    }
}
