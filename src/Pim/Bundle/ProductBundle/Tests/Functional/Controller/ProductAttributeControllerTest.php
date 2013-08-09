<?php

namespace Pim\Bundle\ProductBundle\Tests\Functional\Controller;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductAttributeControllerTest extends ControllerTest
{
    protected function setup()
    {
        $this->markTestSkipped('Due to locale refactoring PIM-861, to replace by behat scenario');
    }

    /**
     * Test related action
     */
    public function testIndex()
    {
        $uri = '/enrich/product-attribute/';

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('div.sub-title:contains("Attributes overview")'));
    }

    /**
     * Test related action
     */
    public function testCreate()
    {
        $uri = '/enrich/product-attribute/create';

        // assert without authentication
        $this->client->request('GET', $uri);
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
        // initialize authentication to call container and get product attribute entity
        $productAttribute = $this->getRepository()->findOneBy(array());
        $uri = '/enrich/product-attribute/edit/'. $productAttribute->getId();

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert with unknown product attribute id and authentication
        $uri = '/enrich/product-attribute/edit/0';
        $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test related action
     */
    public function testRemove()
    {
        // initialize authentication to call container and get product attribute entity
        $productAttribute = $this->getRepository()->findOneBy(array('required' => false));
        $uri = '/enrich/product-attribute/remove/'. $productAttribute->getId();

        // assert without authentication
        $this->client->request('DELETE', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with another request method
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(405, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('DELETE', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert with unknown product attribute id (last removed) and authentication
        $this->client->request('DELETE', $uri, array(), array(), $this->server);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Get tested entity repository
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository()
    {
        return $this->getStorageManager()->getRepository('PimProductBundle:ProductAttribute');
    }

    /**
     * Get attribute group entity repository
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getAttributeGroupRepository()
    {
        return $this->getStorageManager()->getRepository('PimProductBundle:AttributeGroup');
    }
}
