<?php
namespace Pim\Bundle\ConfigBundle\Tests\Functional\Controller;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LanguageControllerTest extends ControllerTest
{

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
