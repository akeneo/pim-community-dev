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
     */
    public function testIndex()
    {
        $uri = '/config/currency/index';

        // assert without authentication
        $crawler = $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test related action
     */
    public function testCreate()
    {
        $uri = '/config/currency/create';

        // assert without authentication
        $crawler = $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test related action
     */
    public function testEdit()
    {
        // initialize authentication to call container and get currency entity
        $currency = $this->getRepository()->findOneBy(array());
        $uri = '/config/currency/edit/'. $currency->getId();

        // assert without authentication
        $crawler = $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert with unknown currency id
        $uri = '/config/currency/edit/0';
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test related action
     */
//     public function testDisable()
//     {
//         // initialize authentication to call container and get currency entity
//         $this->client = static::createClient();
//         $currency = $this->getRepository()->findOneBy(array());
//         $uri = '/config/currency/disable/'. $currency->getId();

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
