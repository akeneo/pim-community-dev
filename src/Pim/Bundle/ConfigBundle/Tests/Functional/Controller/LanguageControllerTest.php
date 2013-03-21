<?php
namespace Pim\Bundle\ConfigBundle\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class LanguageControllerTest extends WebTestCase
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
    protected static $locales = array('en', 'fr');

    /**
     * {@inheritdoc}
     */
    public function run(\PHPUnit_Framework_TestResult $result = null)
    {
        parent::run($result);

        $this->client = static::createClient();
    }

    /**
     * Creates a Client with authentication
     *
     * @param array $options An array of options to pass to the createKernel class
     * @param array $server  An array of server parameters
     *
     * @return \Symfony\Bundle\FrameworkBundle\Client A Client instance
     */
    protected static function createAuthenticatedClient(array $options = array(), array $server = array())
    {
        $server['PHP_AUTH_USER'] = self::AUTH_USER;
        $server['PHP_AUTH_PW']   = self::AUTH_PW;

        return parent::createClient($options, $server);
    }

    /**
     * Get container
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return static::$kernel->getContainer();
    }

    /**
     * Locale provider
     * @static
     *
     * @return multitype:multitype:string
     */
    public static function localeProvider()
    {
        $listLocales = array();

        foreach (static::$locales as $locale) {
            $listLocales[] = array($locale);
        }

        return $listLocales;
    }

    /**
     * Test related action
     * @param string $locale
     *
     * @dataProvider localeProvider
     */
    public function testIndex($locale)
    {
        $uri = '/'. $locale .'/config/language/index';

        // assert without authentication
        $client = static::createClient();
        $crawler = $client->request('GET', $uri);
        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        // assert with authentication
        $client = static::createAuthenticatedClient();
        $crawler = $client->request('GET', $uri);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * Test related action
     * @param string $locale
     *
     * @dataProvider localeProvider
     */
    public function testCreate($locale)
    {
        $uri = '/'. $locale .'/config/language/create';

        // assert without authentication
        $client = static::createClient();
        $crawler = $client->request('GET', $uri);
        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        // assert with authentication
        $client = static::createAuthenticatedClient();
        $crawler = $client->request('GET', $uri);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
