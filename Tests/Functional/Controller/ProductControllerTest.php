<?php
namespace Pim\Bundle\ProductBundle\Tests\Functional\Controller;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductControllerTest extends ControllerTest
{

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
     * @param string $locale
     *
     * @dataProvider localeProvider
     */
    public function testIndex($locale)
    {
        $uri = '/'. $locale .'/product/product/index';

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('table.table:contains("sku-0")'));
    }

    /**
     * Test related action
     * @param string $locale
     *
     * @dataProvider localeProvider
     *
     * @return null
     */
    public function testCreate($locale)
    {
        $uri = '/'. $locale .'/product/product/create';

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert product form well works
        $form = $crawler->filter('form')->reduce(
            function ($node, $i) {
                if ($node->hasAttribute('action')) {
                    $action = $node->getAttribute('action');
                    if (preg_match('#\/product\/product\/create$#', $action)) {
                        return true;
                    }
                }

                return false;
            }
        )->first()->form();

        $values = array(
            'oro_flexibleentity_entity[sku]' => self::PRODUCT_SKU
        );

        $this->submitFormAndAssertFlashbag($form, $values, self::PRODUCT_SAVED_MSG);

        // assert entity well inserted
        $product = $this->getProductManager()->getFlexibleRepository()->findOneBy(array('sku' => self::PRODUCT_SKU));
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\Product', $product);
        $this->assertEquals(self::PRODUCT_SKU, $product->getSku());
    }

    /**
     * Test related action
     * @param string $locale
     *
     * @dataProvider localeProvider
     * @depends testCreate
     */
    public function testEdit($locale)
    {
        // get product entity
        $product = $this->getProductManager()->getFlexibleRepository()->findOneBy(array('sku' => self::PRODUCT_SKU));
        $uri = '/'. $locale .'/product/product/edit/'. $product->getId();

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert with unknown product id and authentication
        $uri = '/'. $locale .'/product/product/edit/0';
        $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test related action
     * @param string $locale
     *
     * @dataProvider localeProvider
     * @depends testCreate
     */
    public function testRemove($locale)
    {
        // initialize authentication to call container and get product entity
        $product = $this->getProductManager()->getFlexibleRepository()->findOneBy(array('sku' => self::PRODUCT_SKU));
        $uri = '/'. $locale .'/product/product/remove/'. $product->getId();

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertFlashBagMessage($crawler, self::PRODUCT_REMOVED_MSG);

        // assert with unknown product id (last removed) and authentication
        $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Get tested entity repository
     *
     * @return FlexibleManager
     */
    protected function getProductManager()
    {
        return static::getContainer()->get('product_manager');
    }
}
