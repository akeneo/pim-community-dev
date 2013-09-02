<?php

namespace Pim\Bundle\CatalogBundle\Tests\Functional\Controller;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductControllerTest extends ControllerTest
{
    /**
     * {@inheritdoc}
     */
    protected function setup()
    {
        $this->markTestSkipped('Due to locale refactoring PIM-861, to replace by behat scenario');
    }

    /**
     * @staticvar string
     */
    const PRODUCT_SKU = 'test-product';

    /**
     * @staticvar string
     */
    const PRODUCT_SAVED_MSG = 'Product successfully saved';

    /**
     * @staticvar string
     */
    const PRODUCT_REMOVED_MSG = 'Product successfully removed';

    /**
     * Test related action
     */
    public function testIndex()
    {
        $uri = '/enrich/product/';

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('div#tree'));
        $this->assertCount(1, $crawler->filter('div#product-grid'));
    }

    /**
     * Get tested entity repository
     *
     * @return FlexibleManager
     */
    protected function getProductManager()
    {
        return static::getContainer()->get('pim_catalog.manager.product');
    }
}
