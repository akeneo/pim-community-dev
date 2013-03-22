<?php
namespace Pim\Bundle\ProductBundle\Tests\Functional\Controller;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductFamilyControllerTest extends ControllerTest
{
    /**
     * Test related action
     * @param string $locale
     *
     * @dataProvider localeProvider
     */
    public function testIndex($locale)
    {
        $uri = '/'. $locale .'/product/product-family/index';

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
        $uri = '/'. $locale .'/product/product-family/create';

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
        // initialize authentication to call container and get product family entity
        $productFamily = $this->getRepository()->findOneBy(array());
        $uri = '/'. $locale .'/product/product-family/edit/'. $productFamily->getId();

        // assert without authentication
        $crawler = $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert with unknown product family id and authentication
        $uri = '/'. $locale .'/product/product-family/edit/0';
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test related action
     * @param string $locale
     *
     * @dataProvider localeProvider
     */
    public function testRemove($locale)
    {
        // initialize authentication to call container and get product family entity
        $productFamily = $this->getRepository()->findOneBy(array());
        $uri = '/'. $locale .'/product/product-family/remove/'. $productFamily->getId();

        // assert without authentication
        $crawler = $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());

        // assert with unknown product family id (last removed) and authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Get tested entity repository
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository()
    {
        return $this->getStorageManager()->getRepository('PimProductBundle:ProductFamily');
    }
}
