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
    const FAMILY_LABEL = 'family name';

    /**
     * @staticvar string
     */
    const FAMILY_CODE = 'familycode';

    /**
     * @staticvar string
     */
    const FAMILY_EDITED_NAME = 'family edited name';

    /**
     * @staticvar string
     */
    const FAMILY_CREATED_MSG = 'Product family successfully created';

    /**
     * @staticvar string
     */
    const FAMILY_SAVED_MSG = 'Product family successfully updated';

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
        $this->assertCount(1, $crawler->filter('html:contains("mug")'));
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
            'pim_product_family[name][label:default]' => self::FAMILY_LABEL,
            'pim_product_family[code]'                => self::FAMILY_CODE,
        );

        $this->submitFormAndAssertFlashbag($form, $values, self::FAMILY_CREATED_MSG);

        // assert entity well inserted
        $family = $this->getRepository()->findOneBy(array('code' => self::FAMILY_CODE));
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\ProductFamily', $family);
        $this->assertEquals(self::FAMILY_LABEL, $family->getLabel());
        $this->assertEquals(self::FAMILY_CODE, $family->getCode());
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
        // get product family entity
        $productFamily = $this->getRepository()->findOneBy(array('code' => self::FAMILY_CODE));
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
            'pim_product_family[name][label:default]' => self::FAMILY_EDITED_NAME,
            'pim_product_family[code]'                => self::FAMILY_CODE,
        );

        $this->submitFormAndAssertFlashbag($form, $values, self::FAMILY_SAVED_MSG);

        // assert entity well inserted
        $family = $this->getRepository()->findOneBy(array('code' => self::FAMILY_CODE));
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\ProductFamily', $family);
        $this->assertEquals(self::FAMILY_EDITED_NAME, $family->getLabel());
        $this->assertEquals(self::FAMILY_CODE, $family->getCode());

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
        // get product family entity
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
