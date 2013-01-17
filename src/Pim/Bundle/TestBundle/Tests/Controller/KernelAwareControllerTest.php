<?php
namespace Pim\Bundle\TestBundle\Tests\Controller;

use Doctrine\ODM\MongoDB\Mapping\Annotations\String;

use Symfony\Component\DomCrawler\Crawler;

use Doctrine\ORM\EntityManager;
use Doctrine\ODM\MongoDB\DocumentManager;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Abstract controller web test case
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @abstract
 */
abstract class KernelAwareControllerTest extends WebTestCase
{
    /**
     * server values for HTTP request (XmlHttpRequest, Content-Type, etc.)
     * @var array
     */
    protected $server = array();

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client;

    /**
     * Tested url pattern
     * @staticvar string
     */
    static protected $testedUrl = '/%%lang%%/%%bundle%%/%%controller%%/%%action%%';

    /**
     * Redirect content
     * @staticvar string
     */
    protected static $redirectContent = '<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="refresh" content="1;url=%%REDIRECT_URL%%" />

        <title>Redirecting to %%REDIRECT_URL%%</title>
    </head>
    <body>
        Redirecting to <a href="%%REDIRECT_URL%%">%%REDIRECT_URL%%</a>.
    </body>
</html>';


    /**
     * Generate url for testing
     * @param string $locale     the locale tested
     * @param string $action     the action tested
     * @param string $controller the controller tested
     * @param string $bundleName the bundle tested
     *
     * @return string
     */
    protected static function prepareUrl($locale, $action, $controller = null, $bundleName = null)
    {
        $controller = ($controller === null) ? static::$controller : $controller;
        $bundleName = ($bundleName === null) ? static::$bundleName : $bundleName;

        $url = self::$testedUrl;

        $url = str_replace('%%lang%%', $locale, $url);
        $url = str_replace('%%bundle%%', $bundleName, $url);
        $url = str_replace('%%controller%%', $controller, $url);
        $url = str_replace('%%action%%', $action, $url);

        echo $url ."\n";

        return $url;
    }

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        static::$kernel = new \AppKernel('test', true);
        static::$kernel->boot();

        $this->container = static::$kernel->getContainer();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        // get connection
        $connection = $this->getEntityManager()->getConnection();

        // get tables list
        $tables = $this->getEntityManager()->getConnection()->getSchemaManager()->listTables();

        // truncate tables
        try {
            // start transaction
            $this->getEntityManager()->getConnection()->beginTransaction();

            // disable foreign keys check
            $connection->query('SET FOREIGN_KEY_CHECKS = 0');

            foreach ($tables as $table) {
                $query = $connection->getDatabasePlatform()->getTruncateTableSQL($table->getName());
                $connection->executeUpdate($query);
            }

            // enable foreign key check
            $connection->query('SET FOREIGN_KEY_CHECKS = 0');

            $this->getEntityManager()->getConnection()->commit();
        } catch (\Exception $e) {
            // rollback if an exception is caught
            $connection->rollBack();
        }

        parent::tearDown();
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * @return DocumentManager
     */
    protected function getDocumentManager()
    {
        return $this->getContainer()->get('doctrine.odm.mongodb.document_manager');
    }

    /**
     * {@inheritdoc}
     */
    public function runTest()
    {
        $this->client = static::createClient();
        $this->server = array();

        parent::runTest();
    }

    /**
     * Get container
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
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
     * Assert a
     * @param unknown_type $url
     */
    protected function assertRedirectTo($url)
    {
        $this->assertTrue($this->client->getResponse()->isRedirection());
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $content = $this->client->getResponse()->getContent();
        $expectedContent = str_replace('%%REDIRECT_URL%%', $url, self::$redirectContent);
        $this->assertEquals($expectedContent, $content);
    }
}