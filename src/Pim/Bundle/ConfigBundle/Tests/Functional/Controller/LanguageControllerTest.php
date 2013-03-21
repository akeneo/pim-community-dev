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

    /**
     * Test related action
     * @param string $locale
     *
     * @dataProvider localeProvider
     */
    public function testEdit($locale)
    {
        // initialize authentication to call container and get language entity
        $client = static::createClient();
        $language = $this->getRepository()->findOneBy(array());
        $uri = '/'. $locale .'/config/language/edit/'. $language->getId();

        // assert without authentication
        $crawler = $client->request('GET', $uri);
        $this->assertEquals(401, $client->getResponse()->getStatusCode());

        // assert with authentication
        $client = static::createAuthenticatedClient();
        $crawler = $client->request('GET', $uri);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // assert with unknown language id
        $uri = '/'. $locale .'/config/language/edit/0';
        $crawler = $client->request('GET', $uri);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    /**
     * Test related action
     * @param string $locale
     *
     * @dataProvider localeProvider
     */
//     public function testDisable($locale)
//     {
//         // initialize authentication to call container and get language entity
//         $client = static::createClient();
//         $language = $this->getRepository()->findOneBy(array());
//         $uri = '/'. $locale .'/config/language/disable/'. $language->getId();

//         // assert without authentication
//         $crawler = $client->request('GET', $uri);
//         $this->assertEquals(401, $client->getResponse()->getStatusCode());

//         // assert with authentication
//         $client = static::createAuthenticatedClient();
//         $crawler = $client->request('GET', $uri);
//         $this->assertEquals(302, $client->getResponse()->getStatusCode());

//         // assert with unknown language id (last removed)
//         $crawler = $client->request('GET', $uri);
//         $this->assertEquals(404, $client->getResponse()->getStatusCode());
//     }

    /**
     * Get tested entity repository
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository()
    {
        return $this->getStorageManager()->getRepository('PimConfigBundle:Language');
    }
}
