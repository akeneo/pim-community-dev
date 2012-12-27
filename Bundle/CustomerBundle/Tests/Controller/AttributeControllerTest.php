<?php

namespace Oro\Bundle\CustomerBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class AttributeControllerTest extends WebTestCase
{
    /**
     * Test related method
     */
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/en/customer/attribute/index');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * Test related method
     */
    public function testInsert()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/en/customer/attribute/insert');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }
}
