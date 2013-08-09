<?php

namespace Pim\Bundle\ProductBundle\Tests\Functional\Controller;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
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
    const GROUP_CODE = 'groupcode';

    /**
     * @staticvar string
     */
    const GROUP_EDITED_CODE = 'groupeditedcode';

    /**
     * @staticvar string
     */
    const GROUP_SAVED_MSG = 'Attribute group successfully saved';

    /**
     * @staticvar string
     */
    const GROUP_REMOVED_MSG = 'Attribute group successfully removed';

    /**
     * Test related action
     */
    public function testIndex()
    {
        $uri = '/enrich/attribute-group/';

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('#pim_product_attributegroup_sort:contains("SEO")'));
    }

    /**
     * Test related action
     *
     * @return null
     */
    public function testCreate()
    {
        $uri = '/enrich/attribute-group/create';

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert attribute group form well works
        $form = $crawler->filter('form')->reduce(
            function ($node, $i) {
                if ($action = $node->attr('action')) {
                    if (preg_match('#\/enrich\/attribute-group\/create$#', $action)) {
                        return true;
                    }
                }

                return false;
            }
        )->first()->form();

        $values = array(
            'pim_attribute_group_form[code]'          => self::GROUP_CODE,
            'pim_attribute_group_form[name][default]' => self::GROUP_NAME,
            'pim_attribute_group_form[sort_order]'    => self::GROUP_ORDER
        );

        $this->submitFormAndAssertFlashbag($form, $values, self::GROUP_SAVED_MSG);

        // assert entity well inserted
        $attributeGroup = $this->getRepository()->findOneBy(array('code' => self::GROUP_CODE));
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\AttributeGroup', $attributeGroup);
        $this->assertEquals(self::GROUP_NAME, $attributeGroup->getName());
        $this->assertEquals(self::GROUP_ORDER, $attributeGroup->getSortOrder());
    }

    /**
     * Test related action
     * @depends testCreate
     *
     * @return null
     */
    public function testEdit()
    {
        // get attribute group entity
        $attributeGroup = $this->getRepository()->findOneBy(array('code' => self::GROUP_CODE));
        $uri = '/enrich/attribute-group/edit/'. $attributeGroup->getId();

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert attribute group form well works
        $form = $crawler->filter('form')->reduce(
            function ($node, $i) {
                if ($action = $node->attr('action')) {
                    if (preg_match('#\/enrich\/attribute-group\/edit/[0-9]*$#', $action)) {
                        return true;
                    }
                }

                return false;
            }
        )->first()->form();

        $values = array(
            'pim_attribute_group_form[code]'          => self::GROUP_EDITED_CODE,
            'pim_attribute_group_form[name][default]' => self::GROUP_NAME,
            'pim_attribute_group_form[sort_order]'    => self::GROUP_ORDER
        );

        $this->submitFormAndAssertFlashbag($form, $values, self::GROUP_SAVED_MSG);

        // assert entity well inserted
        $attributeGroup = $this->getRepository()->findOneBy(array('code' => self::GROUP_EDITED_CODE));
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\AttributeGroup', $attributeGroup);
        $this->assertEquals(self::GROUP_EDITED_CODE, $attributeGroup->getCode());
        $this->assertEquals(self::GROUP_ORDER, $attributeGroup->getSortOrder());

        // assert with unknown attribute group id and authentication
        $uri = '/enrich/attribute-group/edit/0';
        $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test related action
     * @depends testEdit
     */
    public function testRemove()
    {
        // get attribute group entity
        $attributeGroup = $this->getRepository()->findOneBy(array('code' =>self::GROUP_EDITED_CODE));
        $uri = '/enrich/attribute-group/remove/'. $attributeGroup->getId();

        // assert without authentication
        $crawler = $this->client->request('DELETE', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with incorrect method
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(405, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('DELETE', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertFlashBagMessage($crawler, self::GROUP_REMOVED_MSG);

        // assert with unknown attribute group id (last removed) and authentication
        $this->client->request('DELETE', $uri, array(), array(), $this->server);
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
