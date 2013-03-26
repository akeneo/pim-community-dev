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
class ProductFamilyControllerTest extends ControllerTest
{
    /**
     * @staticvar string
     */
    const FAMILY_NAME = 'family name';

    /**
     * @staticvar string
     */
    const FAMILY_DESC = 'family description';

    /**
     * @staticvar string
     */
    const FAMILY_EDITED_NAME = 'family edited name';

    /**
     * @staticvar string
     */
    const FAMILY_SAVED_MSG = 'Product family successfully saved';

    /**
     * @staticvar string
     */
    const FAMILY_REMOVED_MSG ='Product family successfully removed';

    /**
     * Test related action
     * @param string $locale
     *
     * @dataProvider localeProvider
     */
    public function testIndex($locale)
    {
        $uri = '/'. $locale .'/product/product-family/index';

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('table.table:contains("Mug")'));
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
        $uri = '/'. $locale .'/product/product-family/create';

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert family form well works
        $form = $crawler->filter('form')->reduce(
            function ($node, $i) {
                if ($node->hasAttribute('action')) {
                    $action = $node->getAttribute('action');
                    if (preg_match('#\/product\/product-family\/create$#', $action)) {
                        return true;
                    }
                }

                return false;
            }
        )->first()->form();

        $values = array(
            'pim_product_family[name]'        => self::FAMILY_NAME,
            'pim_product_family[description]' => self::FAMILY_DESC,
            'pim_product_family[attributes]'  => array()
        );

        $this->submitFormAndAssertFlashbag($form, $values, self::FAMILY_SAVED_MSG);

        // assert entity well inserted
        $family = $this->getRepository()->findOneBy(array('name' => self::FAMILY_NAME));
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\ProductFamily', $family);
        $this->assertEquals(self::FAMILY_NAME, $family->getName());
        $this->assertEquals(self::FAMILY_DESC, $family->getDescription());
    }

    /**
     * Test related action
     * @param string $locale
     *
     * @dataProvider localeProvider
     * @depends testCreate
     *
     * @return null
     */
    public function testEdit($locale)
    {
        // initialize authentication to call container and get product family entity
        $productFamily = $this->getRepository()->findOneBy(array('name' => self::FAMILY_NAME));
        $uri = '/'. $locale .'/product/product-family/edit/'. $productFamily->getId();

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert family form well works
        $form = $crawler->filter('form')->reduce(
            function ($node, $i) {
                if ($node->hasAttribute('action')) {
                    $action = $node->getAttribute('action');
                    if (preg_match('#\/product\/product-family\/edit/[0-9]*$#', $action)) {
                        return true;
                    }
                }

                return false;
            }
        )->first()->form();

        $values = array(
            'pim_product_family[name]'        => self::FAMILY_EDITED_NAME,
            'pim_product_family[description]' => self::FAMILY_DESC,
            'pim_product_family[attributes]'  => array()
        );

        $this->submitFormAndAssertFlashbag($form, $values, self::FAMILY_SAVED_MSG);

        // assert entity well inserted
        $family = $this->getRepository()->findOneBy(array('name' => self::FAMILY_EDITED_NAME));
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\ProductFamily', $family);
        $this->assertEquals(self::FAMILY_EDITED_NAME, $family->getName());
        $this->assertEquals(self::FAMILY_DESC, $family->getDescription());

        // assert with unknown product family id and authentication
        $uri = '/'. $locale .'/product/product-family/edit/0';
        $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test related action
     * @param string $locale
     *
     * @dataProvider localeProvider
     * @depends testEdit
     */
    public function testRemove($locale)
    {
        // initialize authentication to call container and get product family entity
        $productFamily = $this->getRepository()->findOneBy(array());
        $uri = '/'. $locale .'/product/product-family/remove/'. $productFamily->getId();

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertFlashBagMessage($crawler, self::FAMILY_REMOVED_MSG);

        // assert with unknown product family id (last removed) and authentication
        $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Get tested entity repository
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository()
    {
        return $this->getStorageManager()->getRepository('PimProductBundle:ProductFamily');
    }
}
