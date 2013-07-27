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
class CategoryTreeControllerTest extends ControllerTest
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
    const TREE_CREATED_MSG = 'Tree successfully created';

    /**
     * @staticvar string
     */
    const TREE_SAVED_MSG = 'Tree successfully updated';

    /**
     * @staticvar string
     */
    const CATEGORY_CREATED_MSG = 'Category successfully created';

    /**
     * @staticvar string
     */
    const CATEGORY_SAVED_MSG = 'Category successfully updated';

    /**
     * @staticvar string
     */
    const CATEGORY_REMOVED_MSG = 'Category successfully removed';

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
     * Test create action for a tree
     *
     * @return null
     */
    public function testCreateTree()
    {
        $uri = '/enrich/category-tree/create';

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert tree form well works
        $crawler = $crawler->filter('form')->reduce(
            function ($node, $i) {
                if ($action = $node->attr('action')) {
                    if (preg_match('#\/enrich\/category-tree\/create$#', $action)) {
                        return true;
                    }
                }

                return false;
            }
        )->first();

        // assert fields count
        $this->assertCount(1, $crawler->filter('div > input'));
        $form = $crawler->form();

        $values = array(
            'pim_category[code]'           => self::TREE_CODE,
            'pim_category[title][default]' => self::TREE_TITLE
        );

        $this->submitFormAndAssertFlashbag($form, $values, self::TREE_CREATED_MSG);

        // assert entity well inserted
        $categoryTree = $this->getTreeManager()->getEntityRepository()->findOneBy(array('code' => self::TREE_CODE));
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\Category', $categoryTree);
        $this->assertEquals(self::TREE_CODE, $categoryTree->getCode());
        $this->assertEquals(self::TREE_TITLE, $categoryTree->getTitle());
    }

    /**
     * Test create action for a node
     *
     * @depends testCreateTree
     *
     * @return null
     */
    public function testCreateNode()
    {
        $tree = $this->getTreeManager()->getEntityRepository()->findOneBy(array('code' => self::TREE_CODE));

        $uri = '/enrich/category-tree/create/'. $tree->getId();

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert node form well works
        $crawler = $crawler->filter('form')->reduce(
            function ($node, $i) {
                if ($action = $node->attr('action')) {
                    if (preg_match('#\/enrich\/category-tree\/create/[0-9]*#', $action)) {
                        return true;
                    }
                }

                return false;
            }
        )->first();

        // assert fields count
        $this->assertCount(1, $crawler->filter('div > input'));
        $form = $crawler->form();

        $values = array(
            'pim_category[code]'           => self::NODE_CODE,
            'pim_category[title][default]' => self::NODE_TITLE
        );

        $this->submitFormAndAssertFlashbag($form, $values, self::CATEGORY_CREATED_MSG);

        // assert entity well inserted
        $category = $this->getTreeManager()->getEntityRepository()->findOneBy(array('code' => self::NODE_CODE));
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\Category', $category);
        $this->assertEquals(self::NODE_CODE, $category->getCode());
        $this->assertEquals(self::NODE_TITLE, $category->getTitle());
    }

    /**
     * Test edit action for a tree
     *
     * @depends testCreateTree
     *
     * @return null
     */
    public function testEditTree()
    {
        // get tree
        $categoryTree = $this->getTreeManager()->getEntityRepository()->findOneBy(array('code' => self::TREE_CODE));
        $uri = '/enrich/category-tree/edit/'. $categoryTree->getId();

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert tree form well works
        $crawler = $crawler->filter('form')->reduce(
            function ($node, $i) {
                if ($action = $node->attr('action')) {
                    if (preg_match('#\/enrich\/category-tree\/edit/[0-9]*$#', $action)) {
                        return true;
                    }
                }

                return false;
            }
        )->first();

        // assert fields count
        $this->assertCount(1, $crawler->filter('div > input'));
        $form = $crawler->form();

        $values = array(
            'pim_category[code]'           => self::TREE_EDITED_CODE,
            'pim_category[title][default]' => self::TREE_TITLE
        );

        $this->submitFormAndAssertFlashbag($form, $values, self::TREE_SAVED_MSG);

        // assert entity well edited
        $tree = $this->getTreeManager()->getEntityRepository()->findOneBy(array('code' => self::TREE_EDITED_CODE));
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\Category', $tree);
        $this->assertEquals(self::TREE_EDITED_CODE, $tree->getCode());
        $this->assertEquals(self::TREE_TITLE, $tree->getTitle());

        // assert with unknown tree id and authentication
        $uri = '/enrich/category-tree/edit/0';
        $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test edit action for a node
     *
     * @depends testCreateNode
     *
     * @return null
     */
    public function testEditNode()
    {
        // get node
        $categoryNode = $this->getTreeManager()->getEntityRepository()->findOneBy(array('code' => self::NODE_CODE));
        $uri = '/enrich/category-tree/edit/'. $categoryNode->getId();

        // assert without authentication
        $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert tree form well works
        $crawler = $crawler->filter('form')->reduce(
            function ($node, $i) {
                if ($action = $node->attr('action')) {
                    if (preg_match('#\/enrich\/category-tree\/edit/[0-9]*$#', $action)) {
                        return true;
                    }
                }

                return false;
            }
        )->first();

        // assert fields count
        $this->assertCount(1, $crawler->filter('div > input'));
        $form = $crawler->form();

        $values = array(
            'pim_category[code]'           => self::NODE_EDITED_CODE,
            'pim_category[title][default]' => self::NODE_TITLE
        );

        $this->submitFormAndAssertFlashbag($form, $values, self::CATEGORY_SAVED_MSG);

        // assert entity well edited
        $node = $this->getTreeManager()->getEntityRepository()->findOneBy(array('code' => self::NODE_EDITED_CODE));
        $this->assertInstanceOf('Pim\Bundle\ProductBundle\Entity\Category', $node);
        $this->assertEquals(self::NODE_EDITED_CODE, $node->getCode());
        $this->assertEquals(self::NODE_TITLE, $node->getTitle());
    }

    /**
     * Test related action
     * @depends testEditTree
     */
    public function testRemove()
    {
        // get tree entity
        $tree = $this->getTreeManager()->getEntityRepository()->findOneBy(array('code' => self::NODE_EDITED_CODE));
        $uri = '/enrich/category-tree/'. $tree->getId() .'/remove';

        // assert without authentication
        $crawler = $this->client->request('GET', $uri);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());

        // assert with authentication
        $crawler = $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertFlashBagMessage($crawler, self::CATEGORY_REMOVED_MSG);

        // assert with unknown tree id (last removed) and authentication
        $this->client->request('GET', $uri, array(), array(), $this->server);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Get category tree manager
     *
     * @return \Pim\Bundle\ProductBundle\Model\CategoryManager
     */
    protected function getTreeManager()
    {
        return $this->getContainer()->get('pim_product.manager.category');
    }
}
