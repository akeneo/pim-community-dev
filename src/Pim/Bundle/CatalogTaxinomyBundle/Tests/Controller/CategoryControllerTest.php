<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\Tests\Controller;

use Pim\Bundle\CatalogTaxinomyBundle\Model\CategoryManager;

use Pim\Bundle\CatalogTaxinomyBundle\Entity\Category;

use Doctrine\Common\Collections\ArrayCollection;
/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO : assert return message when status code = 500
 * TODO : assert with wrong post data
 */
class CategoryControllerTest extends AbstractControllerTest
{

    /**
     * category entity test
     * @var Category
     */
    protected $category1;

    /**
     * category entity test
     * @var Category
     */
    protected $category2;

    /**
     * category entity test
     * @var Category
     */
    protected $category3;

    /**
     * category entity test
     * @var Category
     */
    protected $category4;

    /**
     * Base url used for testing
     * @staticvar string
     */
    protected static $baseUrl = '/en_US/catalogtaxinomy/category/';

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->category1 = $this->createCategory('category1');
        $this->category2 = $this->createCategory('category2', $this->category1);
        $this->category3 = $this->createCategory('category3', $this->category1);
        $this->category4 = $this->createCategory('category4', $this->category3);

        $this->getEntityManager()->flush();
    }

    /**
     * Create a category entity
     * @param string   $title  the category title
     * @param Category $parent the parent category if exists
     *
     * @return \Pim\Bundle\CatalogTaxinomyBundle\Entity\Category
     */
    protected function createCategory($title, $parent = null)
    {
        $category = $this->getCategoryManager()->getNewEntityInstance();
        $category->setTitle($title);
        $category->setParent($parent);

        $this->getEntityManager()->persist($category);

        return $category;
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        $this->getEntityManager()->remove($this->category4);
        $this->getEntityManager()->remove($this->category3);
        $this->getEntityManager()->remove($this->category2);
        $this->getEntityManager()->remove($this->category1);

        $this->getEntityManager()->flush();

        parent::tearDown();
    }

    /**
     * Assert entity is a Category entity
     * @param object $entity
     */
    protected function assertInstanceOfCategory($entity)
    {
        $this->assertInstanceOf('\Pim\Bundle\CatalogTaxinomy\Entity\Category', $entity);
    }

    /**
     * Get category manager
     * @return \Pim\Bundle\CatalogTaxinomyBundle\Model\CategoryManager
     */
    protected function getCategoryManager()
    {
        return $this->getContainer()->get('pim.catalog_taxinomy.category_manager');
    }

    /**
     * Assert index content
     * @param Symfony\Component\DomCrawler\Crawler $crawler
     */
    protected function assertIndexContent($crawler)
    {
        $this->assertCount(1, $crawler->filter('div.demo'));
    }

    /**
     * Assert redirect to index
     */
    protected function assertRedirectToIndex()
    {
        $this->assertRedirectTo(self::$baseUrl .'index');
    }

    /**
     * test related action
     */
    public function testIndex()
    {
        $crawler = $this->client->request('GET', self::$baseUrl .'index');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertIndexContent($crawler);
    }

    /**
     * test related action
     */
    public function testChildren()
    {
        // define request
        $this->defineAsXmlHttpRequest();
        $this->setContentType('application/json');
        $categoryId = $this->category1->getId();

        // prepare data
        $getData = array(
            'id' => $categoryId
        );

        // call children view
        $this->client->request('GET', self::$baseUrl .'children', $getData, array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // get content
        $jsonContent = $this->client->getResponse()->getContent();
        $content = json_decode($jsonContent);

        // assert content
        $this->assertCount(2, $content);
        foreach ($content as $category) {
            $this->assertObjectHasAttribute('attr', $category);
            $this->assertObjectHasAttribute('data', $category);
            $this->assertObjectHasAttribute('state', $category);

            $this->assertObjectHasAttribute('id', $category->attr);
            $this->assertObjectHasAttribute('rel', $category->attr);

            $this->assertEquals('folder', $category->attr->rel);
        }

        // call children with POST method -> must return 405 status code
        $postData = array(
            'id' => $categoryId
        );
        $this->client->request('POST', self::$baseUrl .'children', $postData, array(), $this->server);
        $this->assertEquals(405, $this->client->getResponse()->getStatusCode());

        // assert call without XmlHttpRequest redirect to index
        $this->server = array();
        $this->setContentType('application/json');
        $this->client->request('GET', self::$baseUrl .'children', $getData, array(), $this->server);
        $this->assertRedirectToIndex();
    }

    /**
     * test related action
     */
    public function testCreateNode()
    {
        // define request
        $this->defineAsXmlHttpRequest();
        $this->setContentType('application/json');

        // count categories before call create action
        $categories = $this->getCategoryManager()->getCategories();
        $startCount = count($categories);

        // prepare data
        $postData = array(
            'id'    => $this->category1->getId(),
            'title' => 'test'
        );

        // call create view and assert values
        $this->client->request('POST', self::$baseUrl .'createNode', $postData, array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert if category is created
        $categories = $this->getCategoryManager()->getCategories();
        $endCount = count($categories);
        $this->assertEquals($startCount+1, $endCount);

        // assert content
        $jsonContent = $this->client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertObjectHasAttribute('status', $content);
        $this->assertObjectHasAttribute('id', $content);
        $this->assertEquals(1, $content->status);

        // remove inserted category
        $newCategory = $this->getCategoryManager()->getCategory($content->id);
        $this->getEntityManager()->remove($newCategory);
        $this->getEntityManager()->flush();

        // assert wrong method (with GET parameters)
        $this->client->request('GET', self::$baseUrl .'createNode', $postData, array(), $this->server);
        $this->assertEquals(405, $this->client->getResponse()->getStatusCode());

        // assert call without XmlHttpRequest redirect to index
        $this->server = array();
        $this->setContentType('application/json');
        $this->client->request('POST', self::$baseUrl .'createNode', $postData, array(), $this->server);
        $this->assertRedirectToIndex();
    }

    /**
     * test related action
     */
    public function testMoveNode()
    {
        // define request
        $this->defineAsXmlHttpRequest();
        $this->setContentType('application/json');

        // prepare move data
        $postData = array(
            'id'   => $this->category4->getId(),
            'ref'  => $this->category1->getId(),
            'copy' => 0
        );

        // call move view
        $this->client->request('POST', self::$baseUrl .'moveNode', $postData, array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert content
        $jsonContent = $this->client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertObjectHasAttribute('status', $content);
        $this->assertEquals(1, $content->status);

        // assert failed call (move a parent in one of his child)
        $postData = array(
            'id'   => $this->category1->getId(),
            'ref'  => $this->category3->getId(),
            'copy' => 0
        );

        $this->client->request('POST', self::$baseUrl .'moveNode', $postData, array(), $this->server);
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());

        // assert wrong method (with GET parameters)
        $getData = array(
            'id'   => $this->category2->getId(),
            'ref'  => $this->category3->getId(),
            'copy' => 0
        );
        $this->client->request('GET', self::$baseUrl .'moveNode', $getData, array(), $this->server);
        $this->assertEquals(405, $this->client->getResponse()->getStatusCode());

        // assert call without XmlHttpRequest redirect to index
        $this->server = array();
        $this->setContentType('application/json');
        $this->client->request('POST', self::$baseUrl .'moveNode', $postData, array(), $this->server);
        $this->assertRedirectToIndex();
    }

    /**
     * test move node action with copy value
     */
    public function testCopyNode()
    {
        // define request
        $this->defineAsXmlHttpRequest();
        $this->setContentType('application/json');

        // prepare move data
        $postData = array(
            'id'   => $this->category4->getId(),
            'ref'  => $this->category1->getId(),
            'copy' => 1
        );

        // call copy view
        $this->client->request('POST', self::$baseUrl .'moveNode', $postData, array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert content
        $jsonContent = $this->client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertObjectHasAttribute('status', $content);
        $this->assertEquals(1, $content->status);

        // assert wrong method (with GET parameters)
        $getData = array(
            'id'   => $this->category2->getId(),
            'ref'  => $this->category3->getId(),
            'copy' => 1
        );
        $this->client->request('GET', self::$baseUrl .'moveNode', $getData, array(), $this->server);
        $this->assertEquals(405, $this->client->getResponse()->getStatusCode());

        // assert call without XmlHttpRequest redirect to index
        $this->server = array();
        $this->setContentType('application/json');
        $this->client->request('POST', self::$baseUrl .'moveNode', $postData, array(), $this->server);
        $this->assertRedirectToIndex();
    }

    /**
     * test related action
     */
    public function testRemove()
    {
        // define request
        $this->defineAsXmlHttpRequest();
        $this->setContentType('application/json');

        // prepare remove data
        $postData = array(
            'id' => $this->category4->getId()
        );

        // call remove view
        $this->client->request('POST', self::$baseUrl .'removeNode', $postData, array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert content
        $jsonContent = $this->client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertObjectHasAttribute('status', $content);
        $this->assertEquals(1, $content->status);

        // assert failed call with a non-existent category id (category4 is already removed)
        $postData = array(
            'id' => $this->category4->getId()
        );
        $this->client->request('POST', self::$baseUrl .'removeNode', $postData, array(), $this->server);
        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());


        // assert wrong method (with GET parameters)
        $getData = array(
            'id' => $this->category3->getId()
        );
        $this->client->request('GET', self::$baseUrl .'removeNode', $getData, array(), $this->server);
        $this->assertEquals(405, $this->client->getResponse()->getStatusCode());

        // assert call without XmlHttpRequest redirect to index
        $this->server = array();
        $this->setContentType('application/json');
        $this->client->request('POST', self::$baseUrl .'removeNode', $postData, array(), $this->server);
        $this->assertRedirectToIndex();
    }

    /**
     * test related action
     */
    public function testRenameNode()
    {
        // define request
        $this->defineAsXmlHttpRequest();
        $this->setContentType('application/json');

        // prepare rename data
        $postData = array(
            'id'    => $this->category4->getId(),
            'title' => 'test-renamed'
        );

        // call rename view
        $this->client->request('POST', self::$baseUrl .'renameNode', $postData, array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert content
        $jsonContent = $this->client->getResponse()->getContent();
        $content = json_decode($jsonContent);
        $this->assertObjectHasAttribute('status', $content);
        $this->assertEquals(1, $content->status);

        // call without renaming category
        $this->client->request('POST', self::$baseUrl .'renameNode', $postData, array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // TODO (must be fixed in controller) assert failed with a non-existent category id
//         $postData = array(
//             'id'    => $this->category4->getId() + 1,
//             'title' => 'title-renamed'
//         );
//         $this->client->request('POST', self::$baseUrl .'renameNode', $postData, array(), $this->server);
//         $this->assertEquals(500, $this->client->getResponse()->getStatusCode());

        // assert wrong method (with GET parameters)
        $getData = array(
            'id'    => $this->category4->getId(),
            'title' => 'test-renamed-with-get'
        );
        $this->client->request('GET', self::$baseUrl .'renameNode', $getData, array(), $this->server);
        $this->assertEquals(405, $this->client->getResponse()->getStatusCode());

        // assert call without XmlHttpRequest redirect to index
        $this->server = array();
        $this->setContentType('application/json');
        $this->client->request('POST', self::$baseUrl .'renameNode', $postData, array(), $this->server);
        $this->assertRedirectToIndex();
    }

    /**
     * test related action
     */
    public function testSearch()
    {
        // define request
        $this->defineAsXmlHttpRequest();
        $this->setContentType('application/json');

        // prepare search data
        $postData = array(
            'search_str' => 'category'
        );

        // call search view
        $this->client->request('POST', self::$baseUrl .'search', $postData, array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // assert content
        $jsonContent = $this->client->getResponse()->getContent();
        $content = json_decode($jsonContent);
//         $this->assertObjectHasAttribute('status', $content);
        $this->assertCount(4, $content);

        // assert wrong method (with GET parameters)
        $getData = array(
            'search_str' => 'a'
        );
        $this->client->request('GET', self::$baseUrl .'search', $getData, array(), $this->server);
        $this->assertEquals(405, $this->client->getResponse()->getStatusCode());

        // assert call without XmlHttpRequest redirect to index
        $this->server = array();
        $this->setContentType('application/json');
        $this->client->request('POST', self::$baseUrl .'search', $postData, array(), $this->server);
        $this->assertRedirectToIndex();
    }
}