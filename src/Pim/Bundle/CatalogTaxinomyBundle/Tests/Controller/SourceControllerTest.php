<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\Tests\Controller;

use Pim\Bundle\CatalogTaxinomyBundle\Entity\Source;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class SourceControllerTest extends AbstractControllerTest
{

    /**
     * source entity test
     * @var Source
     */
    protected $source1;

    /**
     * source entity test
     * @var Source
     */
    protected $source2;

    /**
     * Base url of controller
     * @staticvar string
     */
    protected static $baseUrl = '/fr/catalogtaxinomy/source/';

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->source1 = $this->createSource('source1');
        $this->source2 = $this->createSource('source2');

        $this->getEntityManager()->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        $this->getEntityManager()->remove($this->source1);
        $this->getEntityManager()->remove($this->source2);

        $this->getEntityManager()->flush();

        parent::tearDown();
    }

    /**
     * Create a source entity
     * @param string $code
     *
     * @return \Pim\Bundle\CatalogTaxinomyBundle\Entity\Source
     */
    protected function createSource($code)
    {
        $source = $this->getSourceManager()->getNewEntityInstance();
        $source->setCode($code);

        $this->getEntityManager()->persist($source);

        return $source;
    }

    /**
     * Get source manager
     * @return \Pim\Bundle\CatalogTaxinomyBundle\Model\SourceManager
     */
    protected function getSourceManager()
    {
        return $this->getContainer()->get('pim.catalog_taxinomy.source_manager');
    }

    /**
     * Assert index content
     * @param Symfony\Component\DomCrawler\Crawler $crawler
     */
    protected function assertIndexContent($crawler)
    {
        $this->assertCount(1, $crawler->filter('div.grid'));
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
    public function testNew()
    {
        $crawler = $this->client->request('GET', self::$baseUrl .'new');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('form'));
    }

    /**
     * test related action
     */
    public function testCreate()
    {
        // prepare data
        $postData = array(
            'code' => 'source-code'
        );

        // call create view and assert values
        $crawler = $this->client->request('POST', self::$baseUrl .'create', $postData);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('form'));

        // TODO : assert success message

        // assert wrong method (with GET parameters)
        $getData = array(
            'code' => 'source-code-get'
        );
        $this->client->request('GET', self::$baseUrl .'create', $getData);
        $this->assertEquals(405, $this->client->getResponse()->getStatusCode());
    }

    /**
     * test related action
     */
    public function testEdit()
    {
        // call edit view and assert values
        $crawler = $this->client->request('GET', self::$baseUrl ."{$this->source2->getId()}/edit");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('form'));
    }

    /**
     * test related action
     */
    public function testUpdate()
    {
        // prepare data
        $postData = array(
            'id'   => $this->source2->getId(),
            'code' => 'source-code'
        );

        // call update view and assert values
        $crawler = $this->client->request('POST', self::$baseUrl ."{$this->source2->getId()}/update", $postData);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('form'));

        // TODO : assert message success

        // assert wrong method (with GET parameters)
        $getData = array(
            'id'   => $this->source2->getId(),
            'code' => 'source-code-get'
        );
        $this->client->request('GET', self::$baseUrl .'create', $getData);
        $this->assertEquals(405, $this->client->getResponse()->getStatusCode());
    }

    /**
     * test related action
     */
    public function testDelete()
    {
        // call delete view and assert values
        $getData = array(
            'id' => $this->source2->getId()
        );
        $this->client->request('GET', self::$baseUrl ."{$this->source2->getId()}/delete", $getData);
        $this->assertRedirectTo(self::$baseUrl .'index');
    }

}
