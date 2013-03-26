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
class AttributeGroupControllerTest extends ControllerTest
{

    /**
     * @staticvar string
     */
    const GROUP_NAME = 'group name';

    /**
     * @staticvar integer
     */
    const GROUP_ORDER = 5;

    /**
     * @staticvar string
     */
    const GROUP_EDITED_NAME = 'group edited name';

    /**
     * @staticvar string
     */
    const GROUP_SAVED_MSG = 'Group successfully saved';

    /**
     * @staticvar string
     */
    const GROUP_REMOVED_MSG = 'Group successfully removed';

    /**
     * Test related action
     * @param string $locale
     *
     * @dataProvider localeProvider
     */
    public function testIndex($locale)
    {
        $uri = '/'. $locale .'/product/attribute-group/index';

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('table.table:contains("SEO")'));
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
        $uri = '/'. $locale .'/product/attribute-group/create';

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert attribute group form well works
        $form = $crawler->filter('form')->reduce(
            function ($node, $i) {
                if ($node->hasAttribute('action')) {
                    $action = $node->getAttribute('action');
                    if (preg_match('#\/product\/attribute-group\/create$#', $action)) {
                        return true;
                    }
                }

                return false;
            }
        )->first()->form();

        $values = array(
            'pim_attribute_group_form[name]'  => self::GROUP_NAME,
            'pim_attribute_group_form[sort_order]' => self::GROUP_ORDER
        );

        $this->submitFormAndAssertFlashbag($form, $values, self::GROUP_SAVED_MSG);

        // assert entity well inserted
        $attributeGroup = $this->getRepository()->findOneBy(array('name' => self::GROUP_NAME));
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\AttributeGroup', $attributeGroup);
        $this->assertEquals(self::GROUP_NAME, $attributeGroup->getName());
        $this->assertEquals(self::GROUP_ORDER, $attributeGroup->getSortOrder());
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
        // initialize authentication to call container and get attribute group entity
        $attributeGroup = $this->getRepository()->findOneBy(array('name' => self::GROUP_NAME));
        $uri = '/'. $locale .'/product/attribute-group/edit/'. $attributeGroup->getId();

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert attribute group form well works
        $form = $crawler->filter('form')->reduce(
            function ($node, $i) {
                if ($node->hasAttribute('action')) {
                    $action = $node->getAttribute('action');
                    if (preg_match('#\/product\/attribute-group\/edit/[0-9]*$#', $action)) {
                        return true;
                    }
                }

                return false;
            }
        )->first()->form();

        $values = array(
            'pim_attribute_group_form[id]'  => $attributeGroup->getId(),
            'pim_attribute_group_form[name]'  => self::GROUP_EDITED_NAME,
            'pim_attribute_group_form[sort_order]' => self::GROUP_ORDER
        );

        $this->submitFormAndAssertFlashbag($form, $values, self::GROUP_SAVED_MSG);

        // assert entity well inserted
        $attributeGroup = $this->getRepository()->findOneBy(array('name' => self::GROUP_EDITED_NAME));
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\AttributeGroup', $attributeGroup);
        $this->assertEquals(self::GROUP_EDITED_NAME, $attributeGroup->getName());
        $this->assertEquals(self::GROUP_ORDER, $attributeGroup->getSortOrder());

        // assert with unknown attribute group id and authentication
        $uri = '/'. $locale .'/product/attribute-group/edit/0';
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
        // initialize authentication to call container and get attribute group entity
        $attributeGroup = $this->getRepository()->findOneBy(array());
        $uri = '/'. $locale .'/product/attribute-group/remove/'. $attributeGroup->getId();

        // assert without authentication
        $crawler = $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert with unknown attribute group id (last removed) and authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Get tested entity repository
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository()
    {
        return $this->getStorageManager()->getRepository('PimProductBundle:AttributeGroup');
    }
}
