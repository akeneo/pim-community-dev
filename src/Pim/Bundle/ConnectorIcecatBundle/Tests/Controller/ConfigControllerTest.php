<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Tests\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ConfigControllerTest extends WebTestCase
{
    /**
     * Base url used for testing
     * @staticvar string
     */
    protected static $baseUrl = '/en_US/icecatconnector/config/';

    /**
     * test related action
     */
    public function testEdit()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', self::$baseUrl .'edit');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('form'));
    }

    /**
     * test related action
     */
    public function testUpdate()
    {
        // get first attribute
        $client = static::createClient();
        $container = $client->getContainer();

        // get page with GET request (error 405 : method not allowed exception)
        $crawler = $client->request('GET', self::$baseUrl .'update');
        $this->assertEquals(405, $client->getResponse()->getStatusCode());

        // TODO : Add post data + assert redirect
        $postData = array();
        $crawler = $client->request('POST', self::$baseUrl .'update', $postData);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

}
