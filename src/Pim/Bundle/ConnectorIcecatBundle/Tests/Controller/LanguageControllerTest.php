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
class LanguageControllerTest extends WebTestCase
{
    /**
     * Base url used for testing
     * @staticvar string
     */
    protected static $baseUrl = '/en_US/icecatconnector/language/';

    /**
     * test related action
     */
    public function testList()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', self::$baseUrl .'list');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('div.grid'));
    }
}
