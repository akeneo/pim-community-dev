<?php

namespace Oro\Bundle\ProductBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class ProductControllerTest extends WebTestCase
{
    /**
     * Test related method
     */
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/en/product/product/index');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * Test related method
     */
    public function testInsert()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/en/product/product/insert');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * Test related method
     */
    public function testTranslate()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/en/product/product/translate');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    /**
     * Test related method
     */
    public function testQueries()
    {
        $client = static::createClient();
        $actions = array(
            '/en/product/product/querylazyload',
            '/en/product/product/queryonlyname',
            '/en/product/product/querynameanddesc',
            '/en/product/product/queryfilterskufield',
            '/en/product/product/querynamefilterskufield',
            '/en/product/product/queryfiltersizeattribute',
            '/en/product/product/queryfiltersizeanddescattributes',
            '/en/product/product/querynameanddesclimit',
            '/en/product/product/querynameanddescorderby',
        );
        foreach ($actions as $action) {
            $crawler = $client->request('GET', $action);
            $this->assertEquals(200, $client->getResponse()->getStatusCode());
        }
    }

}
