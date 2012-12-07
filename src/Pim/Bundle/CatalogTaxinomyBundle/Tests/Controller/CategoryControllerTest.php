<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\Tests\Controller;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CategoryControllerTest extends WebTestCase
{
    /**
     * server values for HTTP request (XMLHttp, Content-Type, etc.)
     * @var array
     */
    protected $server = array();

    /**
     * Base url used for testing
     * @staticvar string
     */
    protected static $baseUrl = '/en_US/catalogtaxinomy/category/';

    /**
     * Actual inserted category
     * @var Pim\Bundle\CatalogTaxinomyBundle\Entity\Category
     */
    protected $categoryInserted;

    /**
     *
     * @var ArrayCollection
     */
    protected $startCategories;

    /**
     *
     * @var ArrayCollection
     */
    protected $endCategories;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
//         $this->server = array();

//         static::$kernel = new \AppKernel('test', true);
//         static::$kernel->boot();

//         $this->container = static::$kernel->getContainer();
//         $this->entityManager = $this->container->get('doctrine')->getEntityManager();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        parent::tearDown();

//         $endCategories = $this->getCategoryManager()->getCategories();

//         $diff = array_diff($this->startCategories, $endCategories);

//         echo count($diff);
//         $this->getCategoryManager()->findAll();
    }

    /**
     * Get container
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->client->getKernel()->getContainer();
    }

    /**
     * Get a service from the container
     * @param string $service The service identifier
     *
     * @return object The associated service
     */
    protected function get($service)
    {
        return $this->getContainer()->get($service);
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
     * Define test as XML HTTP request
     */
    protected function defineAsXmlHttpRequest()
    {
        $this->server['HTTP_X-Requested-With'] = 'XMLHttpRequest';
    }

    /**
     * Define return content type of the request
     * @param string $contentType
     */
    protected function setContentType($contentType)
    {
        $this->server['CONTENT_TYPE'] = $contentType;
    }

    /**
     * test related action
     */
    public function testIndex()
    {
        $client = static::createClient();
        $client->request('GET', self::$baseUrl .'index');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * test related action
     */
    public function testChildren()
    {
        // define request
        $this->defineAsXmlHttpRequest();
        $this->setContentType('application/json');

        $this->client = static::createClient();
        $this->client->request('GET', self::$baseUrl .'children?id=1', array(), array(), $this->server);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // TODO : Assert content type
    }

//     /**
//      * test related action
//      */
//     public function testCreateNode()
//     {
//         // define request
//         $this->defineAsXmlHttpRequest();
//         $this->setContentType('application/json');

//         $postData = array(
//             'id'    => 3,
//             'title' => 'test'
//         );

//         $client = static::createClient();
//         $client->request('POST', self::$baseUrl .'createNode', $postData, array(), $this->server);
//         $this->assertEquals(200, $client->getResponse()->getStatusCode());

//         // TODO : Assert if category is created
//         // TODO : assert content type
//         // TODO : assert failed call
//     }

//     /**
//      * test related action
//      */
//     public function testMoveNode()
//     {
//         // define request
//         $this->defineAsXmlHttpRequest();
//         $this->setContentType('application/json');

//         // prepare move data
//         $postData = array(
//             'id'   => 4,
//             'ref'  => 3,
//             'copy' => 0
//         );

//         $client = static::createClient();
//         $client->request('POST', self::$baseUrl .'moveNode', $postData, array(), $this->server);
//         $this->assertEquals(200, $client->getResponse()->getStatusCode());

//         // TODO : assert if category is moved
//         // TODO : assert content type
//         // TODO : assert failed call
//     }

//     /**
//      * test move node action with copy value
//      */
//     public function testCopyNode()
//     {
//         // define request
//         $this->defineAsXmlHttpRequest();
//         $this->setContentType('application/json');

//         // prepare copy data
//         $postData = array(
//             'id'   => 3,
//             'ref'  => 2,
//             'copy' => 1
//         );

//         $client = static::createClient();
//         $client->request('POST', self::$baseUrl .'moveNode', $postData, array(), $this->server);
//         $this->assertEquals(200, $client->getResponse()->getStatusCode());

//         // TODO : assert if category is copied
//         // TODO : assert content type
//         // TODO : assert failed call
//     }

//     /**
//      * test related action
//      */
//     public function testRemove()
//     {
//         // define request
//         $this->defineAsXmlHttpRequest();
//         $this->setContentType('application/json');

//         // prepare remove data
//         $postData = array(
//             'id' => 3
//         );

//         $client = static::createClient();
//         $client->request('POST', self::$baseUrl .'removeNode', $postData, array(), $this->server);
//         $this->assertEquals(200, $client->getResponse()->getStatusCode());

//         // TODO : assert if category is removed
//         // TODO : assert content type
//         // TODO : assert failed call
//     }

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
            'id'    => 4,
            'title' => 'test'
        );

        $client = static::createClient();
        $client->request('POST', self::$baseUrl .'renameNode', $postData, array(), $this->server);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // TODO : assert if category is renamed
        // TODO : assert content type
        // TODO : assert failed call
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
            'search_str' => 'a'
        );

        $client = static::createClient();
        $client->request('POST', self::$baseUrl .'search', $postData, array(), $this->server);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // TODO : assert categories searched
        // TODO : assert content type
        // TODO : assert failed call
    }
}