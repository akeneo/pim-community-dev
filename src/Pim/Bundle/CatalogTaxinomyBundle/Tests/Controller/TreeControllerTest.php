<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\Tests\Controller;

use Pim\Bundle\CatalogTaxinomyBundle\Entity\CategoryRepository;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
/**
 * Test rekated class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class TreeControllerTest extends WebTestCase
{
    /**
     * server values for HTTP request (XMLHttp, Content-Type, etc.)
     * @var array
     */
    protected $server = array();

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->server = array();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        parent::tearDown();
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
        $crawler = $client->request('GET', '/en_US/catalogtaxinomy/tree/index');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        // TODO : Assert redirect to tree/tree
    }

    /**
     * test related action
     */
    public function testTree()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en_US/catalogtaxinomy/tree/tree');
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

        $client = static::createClient();
        $crawler = $client->request('GET', '/en_US/catalogtaxinomy/tree/children?id=1', array(), array(), $this->server);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // TODO : Assert content type
    }

    /**
     * test related action
     */
    public function testCreateNode()
    {
        // define request
        $this->defineAsXmlHttpRequest();
        $this->setContentType('application/json');

        $postData = array(
            'id'    => 3,
            'title' => 'test'
        );
        $client = static::createClient();
        $crawler = $client->request('POST', '/en_US/catalogtaxinomy/tree/createNode', $postData, array(), $this->server);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // TODO : Assert if category is created
        // TODO : assert content type
        // TODO : assert failed call
    }

    /**
     * test related action
     */
    public function testMoveNode()
    {
        // define request
//         $this->defineAsXmlHttpRequest();
//         $this->setContentType('application/json');

//         // prepare move data
//         $postData = array(
//             'id'   => 4,
//             'ref'  => 3,
//             'copy' => 0
//         );

//         $client = static::createClient();
//         $crawler = $client->request('POST', '/en_US/catalogtaxinomy/tree/moveNode', $postData, array(), $this->server);
//         $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // TODO : assert if category is moved
        // TODO : assert content type
        // TODO : assert failed call
    }

    /**
     * test move node action with copy value
     */
    public function testCopyNode()
    {
        // define request
//         $this->defineAsXmlHttpRequest();
//         $this->setContentType('application/json');

//         // prepare copy data
//         $postData = array(
//             'id'   => 3,
//             'ref'  => 2,
//             'copy' => 1
//         );

//         $client = static::createClient();
//         $crawler = $client->request('POST', '/en_US/catalogtaxinomy/tree/moveNode', $postData, array(), $this->server);
//         $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // TODO : assert if category is copied
        // TODO : assert content type
        // TODO : assert failed call
    }

    /**
     * test related action
     */
    public function testRemove()
    {
        // define request
//         $this->defineAsXmlHttpRequest();
//         $this->setContentType('application/json');

//         // prepare remove data
//         $postData = array(
//             'id' => 3
//         );

//         $client = static::createClient();
//         $crawler = $client->request('POST', '/en_US/catalogtaxinomy/tree/removeNode', $postData, array(), $this->server);
//         $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // TODO : assert if category is removed
        // TODO : assert content type
        // TODO : assert failed call
    }

    /**
     * test related action
     */
    public function testRenameNode()
    {
        // define request
//         $this->defineAsXmlHttpRequest();
//         $this->setContentType('application/json');

//         // prepare rename data
//         $postData = array(
//             'id'    => 3,
//             'title' => 'test'
//         );

//         $client = static::createClient();
//         $crawler = $client->request('POST', '/en_US/catalogtaxinomy/tree/renameNode', $postData, array(), $this->server);
//         $this->assertEquals(200, $client->getResponse()->getStatusCode());

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
        $crawler = $client->request('POST', '/en_US/catalogtaxinomy/tree/search', $postData, array(), $this->server);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // TODO : assert categories searched
        // TODO : assert content type
        // TODO : assert failed call
    }
}