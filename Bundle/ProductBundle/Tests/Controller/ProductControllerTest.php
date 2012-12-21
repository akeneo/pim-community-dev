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

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
