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
class AttributeGroupControllerTest extends ControllerTest
{
    /**
     * Test related action
     * @param string $locale
     *
     * @dataProvider localeProvider
     */
    public function testIndex($locale)
    {
        $uri = '/'. $locale .'/product/attribute-group/index';

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
        $uri = '/'. $locale .'/product/attribute-group/create';

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
        // initialize authentication to call container and get attribute group entity
        $attributeGroup = $this->getRepository()->findOneBy(array());
        $uri = '/'. $locale .'/product/attribute-group/edit/'. $attributeGroup->getId();

        // assert without authentication
        $crawler = $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert with unknown attribute group id and authentication
        $uri = '/'. $locale .'/product/attribute-group/edit/0';
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
        // initialize authentication to call container and get attribute group entity
        $attributeGroup = $this->getRepository()->findOneBy(array());
        $uri = '/'. $locale .'/product/attribute-group/remove/'. $attributeGroup->getId();

        // assert without authentication
        $crawler = $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // TODO: @Romain fix mapping problem between group and attributes
        // assert with authentication
        // $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        // $this->assertEquals(302, $this->client->getResponse()->getStatusCode());

        // assert with unknown attribute group id (last removed) and authentication
        //$crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        //$this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Get tested entity repository
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository()
    {
        return $this->getStorageManager()->getRepository('PimProductBundle:AttributeGroup');
    }
}
