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
class ProductControllerTest extends WebTestCase
{
    /**
     * test related action
     */
    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/fr/catalog/product/index');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('div.grid'));
    }

    /**
     * test related action
     */
    public function testEdit()
    {
        // get first entity
        $client = static::createClient();
        $container = $client->getContainer();
        $product = $container->get('pim.catalog.product_manager')->getEntityRepository()->findAll()->getSingleResult();
        $this->assertNotNull($product);
        // get page
        $crawler = $client->request('GET', "/fr/catalog/product/{$product->getId()}/edit");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('form'));
    }

}