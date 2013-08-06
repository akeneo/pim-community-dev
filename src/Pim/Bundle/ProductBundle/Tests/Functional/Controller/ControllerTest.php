<?php

namespace Pim\Bundle\ProductBundle\Tests\Functional\Controller;

use Symfony\Component\DomCrawler\Form;

use Symfony\Component\DomCrawler\Crawler;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Abstract test controller class to avoid duplicated code
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @abstract
 */
abstract class ControllerTest extends WebTestCase
{
    /**
     * Authentication username
     * @staticvar string
     */
    const AUTH_USER = 'admin@example.com';

    /**
     * Authentication password
     * @staticvar string
     */
    const AUTH_PW   = 'admin';

    /**
     * List of locales to test
     * @staticvar multitype:string
     */
    protected static $locales = array('en_US');

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
    */
    protected $client;

    /**
     * Configuration for client request
     * @var multitype:string
     */
    protected $server = array();

    /**
     * {@inheritdoc}
    */
    protected function setUp()
    {
        parent::setUp();

        // create client in setUp allow to use container then
        $this->client = static::createClient();
        $this->prepareServerValues();
    }

    /**
     * {@inheritdoc}
     */
    protected static function createClient(array $options = array(), array $server = array(), $followRedirects = true)
    {
        if (!isset($options['debug'])) {
            $options['debug'] = false;
        }

        $client = parent::createClient($options, $server);
        if ($followRedirects === true) {
            $client->followRedirects();
        }

        return $client;
    }

    /**
     * Get object manager
     *
     * @static
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected static function getStorageManager()
    {
        return static::getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * Prepare server values
     */
    protected function prepareServerValues()
    {
        $this->server['PHP_AUTH_USER'] = self::AUTH_USER;
        $this->server['PHP_AUTH_PW']   = self::AUTH_PW;
    }

    /**
     * Get container
     *
     * @static
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected static function getContainer()
    {
        if (!static::$kernel) {
            throw new \Exception('Kernel not instanciate ! Please create client before call container');
        }

        return static::$kernel->getContainer();
    }

    /**
     * Get translator component
     *
     * @return \Symfony\Bundle\FrameworkBundle\Translation\Translator
     */
    protected function getTranslator()
    {
        return static::getContainer()->get('translator');
    }

    /**
     * Submit form and assert flashbag
     * @param \Symfony\Component\DomCrawler\Form $form    Form crawler
     * @param multitype:string                   $values  Form values
     * @param string                             $message Flashbag message
     */
    protected function submitFormAndAssertFlashbag(Form $form, $values, $message)
    {
        $crawler = $this->client->submit($form, $values);
        $this->assertFlashBagMessage($crawler, $message);
    }

    /**
     * Assert flashbag message
     * @param \Symfony\Component\DomCrawler\Crawler $crawler Form crawler
     * @param string                                $message Flashbag message
     */
    protected function assertFlashBagMessage(Crawler $crawler, $message)
    {
        $this->markTestIncomplete('The javascript flash message seems to disappear before the crawler can find it.');
        $i18nMessage = $this->getTranslator()->trans($message);
        $flashbagSelector = 'div.alert-success';

        try {
            $text = $crawler->filter($flashbagSelector)->text();
        } catch (\InvalidArgumentException $e) {
            $text = "";
        }

        $this->assertNotEmpty($text, 'Unable to find the flash bag message using selector '.$flashbagSelector);
        $this->assertContains($i18nMessage, $text);
    }

    /**
     * Locale provider
     * Override $locales static variable
     *
     * @final
     * @static
     *
     * @return multitype:multitype:string
     */
    final public static function localeProvider()
    {
        $listLocales = array();

        foreach (static::$locales as $locale) {
            $listLocales[] = array($locale);
        }

        return $listLocales;
    }
}
