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
class ClassificationTreeControllerTest extends ControllerTest
{

    /**
     * @staticvar string
     */
    const TREE_CODE = 'tree-code';

    /**
     * @staticvar string
     */
    const TREE_TITLE = 'Tree title';

    /**
     * @staticvar string
     */
    const TREE_EDITED_CODE = 'tree-edited-code';

    /**
     * @staticvar string
     */
    const NODE_CODE = 'node-code';

    /**
     * @staticvar string
     */
    const NODE_TITLE = 'Node title';

    /**
     * @staticvar string
     */
    const NODE_EDITED_CODE = 'node-edited-code';

    /**
     * @staticvar integer
     */
    const NODE_IS_DYNAMIC = 1;

    /**
     * @staticvar string
     */
    const SEGMENT_SAVED_MSG = 'Product segment successfully saved';

    /**
     * @staticvar string
     */
    const SEGMENT_REMOVED_MSG = 'Product segment successfully removed';

    /**
     * Test related action
     * @param string $locale
     *
     * @dataProvider localeProvider
     */
    public function testIndex($locale)
    {
        $uri = '/'. $locale .'/product/classification-tree/index';

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('table.table:contains("Tree")'));
    }

    /**
     * Test create action for a tree
     *
     * @param string $locale
     *
     * @dataProvider localeProvider
     *
     * @return null
     */
    public function testCreateTree($locale)
    {
        $uri = '/'. $locale .'/product/classification-tree/create';

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert tree form well works
        $crawler = $crawler->filter('form')->reduce(
            function ($node, $i) {
                if ($node->hasAttribute('action')) {
                    $action = $node->getAttribute('action');
                    if (preg_match('#\/product\/classification-tree\/create#', $action)) {
                        return true;
                    }
                }

                return false;
            }
        )->first();

        // assert fields count
        $this->assertCount(2, $crawler->filter('div > input'));
        $form = $crawler->form();

        $values = array(
            'pim_product_segment[code]'  => self::TREE_CODE,
            'pim_product_segment[title]' => self::TREE_TITLE
        );

        // TODO : Must be replace by submitFormAndAssertFlashbag method
        $this->client->submit($form, $values);
//         $this->submitFormAndAssertFlashbag($form, $values, self::SEGMENT_SAVED_MSG);

        // assert entity well inserted
        $segmentTree = $this->getTreeManager()->getEntityRepository()->findOneBy(array('code' => self::TREE_CODE));
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\ProductSegment', $segmentTree);
        $this->assertEquals(self::TREE_CODE, $segmentTree->getCode());
        $this->assertEquals(self::TREE_TITLE, $segmentTree->getTitle());
    }

    /**
     * Test create action for a node
     *
     * @param string $locale
     *
     * @dataProvider localeProvider
     * @depends testCreateTree
     *
     * @return null
     */
    public function testCreateNode($locale)
    {
        $tree = $this->getTreeManager()->getEntityRepository()->findOneBy(array('code' => self::TREE_CODE));

        $uri = '/'. $locale .'/product/classification-tree/create/'. $tree->getId();

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert node form well works
        $crawler = $crawler->filter('form')->reduce(
            function ($node, $i) {
                if ($node->hasAttribute('action')) {
                    $action = $node->getAttribute('action');
                    if (preg_match('#\/product\/classification-tree\/create/[0-9]*#', $action)) {
                        return true;
                    }
                }

                return false;
            }
        )->first();

        // assert fields count
        $this->assertCount(3, $crawler->filter('div > input'));
        $form = $crawler->form();

        $values = array(
            'pim_product_segment[code]'      => self::NODE_CODE,
            'pim_product_segment[title]'     => self::NODE_TITLE,
            'pim_product_segment[isDynamic]' => self::NODE_IS_DYNAMIC
        );

        // TODO : Must be replace by submitFormAndAssertFlashbag method
        $this->client->submit($form, $values);
//         $this->submitFormAndAssertFlashbag($form, $values, self::SEGMENT_SAVED_MSG);

        // assert entity well inserted
        $segment = $this->getTreeManager()->getEntityRepository()->findOneBy(array('code' => self::NODE_CODE));
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\ProductSegment', $segment);
        $this->assertEquals(self::NODE_CODE, $segment->getCode());
        $this->assertEquals(self::NODE_TITLE, $segment->getTitle());
        $this->assertEquals(self::NODE_IS_DYNAMIC, $segment->getIsDynamic());
    }

    /**
     * Test edit action for a tree
     *
     * @param string $locale
     *
     * @dataProvider localeProvider
     * @depends testCreateTree
     *
     * @return null
     */
    public function testEditTree($locale)
    {
        // get tree
        $segmentTree = $this->getTreeManager()->getEntityRepository()->findOneBy(array('code' => self::TREE_CODE));
        $uri = '/'. $locale .'/product/classification-tree/edit/'. $segmentTree->getId();

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert tree form well works
        $crawler = $crawler->filter('form')->reduce(
            function ($node, $i) {
                if ($node->hasAttribute('action')) {
                    $action = $node->getAttribute('action');
                    if (preg_match('#\/product\/classification-tree\/edit/[0-9]*$#', $action)) {
                        return true;
                    }
                }

                return false;
            }
        )->first();

        // assert fields count
        $this->assertCount(2, $crawler->filter('div > input'));
        $form = $crawler->form();

        $values = array(
            'pim_product_segment[code]'  => self::TREE_EDITED_CODE,
            'pim_product_segment[title]' => self::TREE_TITLE
        );

        $this->client->submit($form, $values);
        //$this->submitFormAndAssertFlashbag($form, $values, self::SEGMENT_SAVED_MSG);

        // assert entity well edited
        $tree = $this->getTreeManager()->getEntityRepository()->findOneBy(array('code' => self::TREE_EDITED_CODE));
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\ProductSegment', $tree);
        $this->assertEquals(self::TREE_EDITED_CODE, $tree->getCode());
        $this->assertEquals(self::TREE_TITLE, $tree->getTitle());

        // assert with unknown tree id and authentication
        $uri = '/'. $locale .'/product/classification-tree/edit/0';
        $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test edit action for a node
     *
     * @param string $locale
     *
     * @dataProvider localeProvider
     * @depends testCreateNode
     *
     * @return null
     */
    public function testEditNode($locale)
    {
        // get node
        $segmentNode = $this->getTreeManager()->getEntityRepository()->findOneBy(array('code' => self::NODE_CODE));
        $uri = '/'. $locale .'/product/classification-tree/edit/'. $segmentNode->getId();

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert tree form well works
        $crawler = $crawler->filter('form')->reduce(
            function ($node, $i) {
                if ($node->hasAttribute('action')) {
                    $action = $node->getAttribute('action');
                    if (preg_match('#\/product\/classification-tree\/edit/[0-9]*$#', $action)) {
                        return true;
                    }
                }

                return false;
            }
        )->first();

        // assert fields count
        $this->assertCount(3, $crawler->filter('div > input'));
        $form = $crawler->form();

        $values = array(
            'pim_product_segment[code]'  => self::NODE_EDITED_CODE,
            'pim_product_segment[title]' => self::NODE_TITLE,
            'pim_product_segment[isDynamic]' => self::NODE_IS_DYNAMIC
        );

        $this->client->submit($form, $values);
        //$this->submitFormAndAssertFlashbag($form, $values, self::SEGMENT_SAVED_MSG);

        // assert entity well edited
        $node = $this->getTreeManager()->getEntityRepository()->findOneBy(array('code' => self::NODE_EDITED_CODE));
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\ProductSegment', $node);
        $this->assertEquals(self::NODE_EDITED_CODE, $node->getCode());
        $this->assertEquals(self::NODE_TITLE, $node->getTitle());
        $this->assertEquals(self::NODE_IS_DYNAMIC, $node->getIsDynamic());
    }

    /**
     * Test related action
     * @param string $locale
     *
     * @dataProvider localeProvider
     * @depends testEditTree
     */
    public function testRemove($locale)
    {
        // get tree entity
        $tree = $this->getTreeManager()->getEntityRepository()->findOneBy(array('code' => self::TREE_EDITED_CODE));
        $uri = '/'. $locale .'/product/classification-tree/remove/'. $tree->getId();

        // assert without authentication
        $crawler = $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        // TODO : Must be replace by submitFormAndAssertFlashbag method
//         $this->assertFlashBagMessage($crawler, self::SEGMENT_REMOVED_MSG);

        // assert with unknown tree id (last removed) and authentication
        $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Get classification tree manager
     *
     * @return \Pim\Bundle\ProductBundle\Model\ProductSegmentManager
     */
    protected function getTreeManager()
    {
        return $this->getContainer()->get('pim_product.classification_tree_manager');
    }
}
