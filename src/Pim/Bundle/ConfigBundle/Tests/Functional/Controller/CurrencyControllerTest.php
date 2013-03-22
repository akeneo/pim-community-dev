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
class CurrencyControllerTest extends ControllerTest
{

    /**
     * Test related action
     * @param string $locale
     *
     * @dataProvider localeProvider
     */
    public function testIndex($locale)
    {
        $uri = '/'. $locale .'/config/currency/index';

        // assert without authentication
        $crawler = $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test related action
     * @param string $locale
     *
     * @dataProvider localeProvider
     */
    public function testCreate($locale)
    {
        $uri = '/'. $locale .'/config/currency/create';

        // assert without authentication
        $crawler = $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test related action
     * @param string $locale
     *
     * @dataProvider localeProvider
     */
    public function testEdit($locale)
    {
        // initialize authentication to call container and get currency entity
        $currency = $this->getRepository()->findOneBy(array());
        $uri = '/'. $locale .'/config/currency/edit/'. $currency->getId();

        // assert without authentication
        $crawler = $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert with unknown currency id
        $uri = '/'. $locale .'/config/currency/edit/0';
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test related action
     * @param string $locale
     *
     * @dataProvider localeProvider
     */
//     public function testDisable($locale)
//     {
//         // initialize authentication to call container and get currency entity
//         $this->client = static::createClient();
//         $currency = $this->getRepository()->findOneBy(array());
//         $uri = '/'. $locale .'/config/currency/disable/'. $currency->getId();

//         // assert without authentication
//         $crawler = $this->client->request('GET', $uri);
//         $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

//         // assert with authentication
//         $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
//         $this->assertEquals(302, $this->client->getResponse()->getStatusCode());

//         // assert with unknown currency id (last removed)
//         $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
//         $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
//     }

    /**
     * Get tested entity repository
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository()
    {
        return $this->getStorageManager()->getRepository('PimConfigBundle:Currency');
    }
}
