<?php

namespace Pim\Bundle\CatalogBundle\Tests\Functional\Controller;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyControllerTest extends ControllerTest
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
    const FAMILY_CREATED_MSG = 'Family successfully created';

    /**
     * @staticvar string
     */
    const FAMILY_SAVED_MSG = 'Family successfully updated';

    /**
     * @staticvar string
     */
    const FAMILY_REMOVED_MSG ='Family successfully removed';

    /**
     * {@inheritdoc}
     */
    protected function setup()
    {
        $this->markTestSkipped('Due to locale refactoring PIM-861, to replace by behat scenario');
    }

    /**
     * Test related action
     */
    public function testIndex()
    {
        $uri = '/enrich/family/';

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('html:contains("Mug")'));
    }

    /**
     * Test related action
     *
     * @return null
     */
    public function testCreate()
    {
        $uri = '/enrich/family/create';

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert family form well works
        $form = $crawler->filter('form')->reduce(
            function ($node, $i) {
                if ($action = $node->attr('action')) {
                    if (preg_match('#\/enrich\/family\/create$#', $action)) {
                        return true;
                    }
                }

                return false;
            }
        )->first()->form();

        $values = array(
            'pim_family_form[label][en_US]' => self::FAMILY_LABEL,
            'pim_family_form[code]'         => self::FAMILY_CODE,
        );

        $this->submitFormAndAssertFlashbag($form, $values, self::FAMILY_CREATED_MSG);

        // assert entity well inserted
        $family = $this->getRepository()->findOneBy(array('code' => self::FAMILY_CODE));
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\Family', $family);
        $this->assertEquals(self::FAMILY_LABEL, $family->getLabel());
        $this->assertEquals(self::FAMILY_CODE, $family->getCode());
    }

    /**
     * Test related action
     * @depends testCreate
     *
     * @return null
     */
    public function testEdit()
    {
        // get family entity
        $family = $this->getRepository()->findOneBy(array('code' => self::FAMILY_CODE));
        $uri = '/enrich/family/edit/'. $family->getId();

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
                    if (preg_match('#\/enrich\/family\/edit/[0-9]*$#', $action)) {
                        return true;
                    }
                }

                return false;
            }
        )->first()->form();

        $values = array(
            'pim_family_form[label][en_US]' => self::FAMILY_EDITED_NAME,
            'pim_family_form[code]'         => self::FAMILY_CODE,
        );

        $this->submitFormAndAssertFlashbag($form, $values, self::FAMILY_SAVED_MSG);

        // assert entity well inserted
        $family = $this->getRepository()->findOneBy(array('code' => self::FAMILY_CODE));
        $this->assertInstanceOf('Pim\Bundle\CatalogBundle\Entity\Family', $family);
        $this->assertEquals(self::FAMILY_EDITED_NAME, $family->getLabel());
        $this->assertEquals(self::FAMILY_CODE, $family->getCode());

        // assert with unknown family id and authentication
        $uri = '/enrich/family/edit/0';
        $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test related action
     * @depends testEdit
     */
    public function testRemove()
    {
        // get family entity
        $family = $this->getRepository()->findOneBy(array('code' => self::FAMILY_CODE));
        $uri = '/enrich/family/remove/'. $family->getId();

        // assert without authentication
        $this->client->request('DELETE', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('DELETE', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertFlashBagMessage($crawler, self::FAMILY_REMOVED_MSG);
    }

    /**
     * Get tested entity repository
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository()
    {
        return $this->getStorageManager()->getRepository('PimCatalogBundle:Family');
    }
}
