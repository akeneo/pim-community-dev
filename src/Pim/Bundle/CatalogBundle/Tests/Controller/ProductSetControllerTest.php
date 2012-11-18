<?php
namespace Pim\Bundle\CatalogBundle\Tests\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductSetControllerTest extends WebTestCase
{
    /**
     * test related action
     */
    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/fr/catalog/productset/index');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('div.grid'));
    }

    /**
     * test related action
     */
    public function testNew()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/fr/catalog/productset/new');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('form'));
    }

    /**
     * test related action
     */
    public function testEdit()
    {
        // get first set
        $client = static::createClient();
        $container = $client->getContainer();
        $set = $container->get('pim.catalog.product_manager')->getSetRepository()->findAll()->getSingleResult();
        $this->assertNotNull($set);
        // get page
        $crawler = $client->request('GET', "/fr/catalog/productset/{$set->getId()}/edit");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(2, $crawler->filter('form'));
    }

}