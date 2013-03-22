<?php
namespace Pim\Bundle\ConfigBundle\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Abstract test controller class to avoid duplicated code
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
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
    protected static $locales = array('en');

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
     * Get object manager
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getStorageManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
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
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        if (!static::$kernel) {
            throw new \Exception('Kernel not instanciate ! Please create client before call container');
        }

        return static::$kernel->getContainer();
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
