<?php

namespace Oro\Bundle\ProductBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class DefaultControllerTest extends WebTestCase
{
    /**
     * Test related method
     */
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/product/default/index');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
