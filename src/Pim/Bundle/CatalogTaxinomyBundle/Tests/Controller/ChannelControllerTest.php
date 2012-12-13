<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\Tests\Controller;

use Pim\Bundle\CatalogTaxinomyBundle\Entity\Channel;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ChannelControllerTest extends AbstractControllerTest
{

    /**
     * channel entity test
     * @var Channel
     */
    protected $channel1;

    /**
     * channel entity test
     * @var Channel
     */
    protected $channel2;

    /**
     * Base url of controller
     * @staticvar string
     */
    protected static $baseUrl = '/fr/catalogtaxinomy/channel/';

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->channel1 = $this->createChannel('channel1');
        $this->channel2 = $this->createChannel('channel2');

        $this->getEntityManager()->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        $this->getEntityManager()->remove($this->channel1);
        $this->getEntityManager()->remove($this->channel2);

        $this->getEntityManager()->flush();

        parent::tearDown();
    }

    /**
     * Create a channel entity
     * @param string $code
     *
     * @return \Pim\Bundle\CatalogTaxinomyBundle\Entity\Channel
     */
    protected function createChannel($code)
    {
        $channel = $this->getChannelManager()->getNewEntityInstance();
        $channel->setCode($code);

        $this->getEntityManager()->persist($channel);

        return $channel;
    }

    /**
     * Get channel manager
     *
     * @return \Pim\Bundle\CatalogTaxinomyBundle\Model\ChannelManager
     */
    protected function getChannelManager()
    {
        return $this->getContainer()->get('pim.catalog_taxinomy.channel_manager');
    }

    /**
     * assert index content
     * @param \Symfony\Component\DomCrawler\Crawler $crawler
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
            'code' => 'channel-code'
        );

        // call create view and assert values
        $crawler = $this->client->request('POST', self::$baseUrl .'create', $postData);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('form'));

        // TODO : assert success message

        // assert wrong method (with GET parameters)
        $getData = array(
            'code' => 'channel-code-get'
        );
        $this->client->request('GET', self::$baseUrl .'create', $getData);
        $this->assertEquals(405, $this->client->getResponse()->getStatusCode());

        // assert form call
        $crawler = $this->client->request('GET', self::$baseUrl .'new');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('form'));
        // get form
        $form = $crawler->selectButton('edit-form-submit')->form();
        // set some values
        $timestamp = str_replace('.', '', microtime(true));
        $form['pim_catalogtaxinomy_channel[code]'] = 'channel-'.$timestamp;
        // submit the form
        $crawler = $this->client->submit($form);
    }

    /**
     * test related class
     */
    public function testEdit()
    {
        // call edit view and assert values
        $crawler = $this->client->request('GET', self::$baseUrl ."{$this->channel2->getId()}/edit");
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
            'id'   => $this->channel2->getId(),
            'code' => 'channel-code'
        );

        // call update view and assert values
        $crawler = $this->client->request('POST', self::$baseUrl ."{$this->channel2->getId()}/update", $postData);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('form'));

        // TODO : assert message success

        // assert wrong method (with GET parameters)
        $getData = array(
            'id'   => $this->channel2->getId(),
            'code' => 'channel-code-get'
        );
        $this->client->request('GET', self::$baseUrl .'create', $getData);
        $this->assertEquals(405, $this->client->getResponse()->getStatusCode());

        // assert form call
        $crawler = $this->client->request('GET', self::$baseUrl ."{$this->channel2->getId()}/edit");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('form'));
        // get form
        $form = $crawler->selectButton('edit-form-submit')->form();
        // set some values
        $timestamp = str_replace('.', '', microtime(true));
        $form['pim_catalogtaxinomy_channel[code]'] = 'channel-'.$timestamp;
        // submit the form
        $crawler = $this->client->submit($form);
    }

    /**
     * test related action
     */
    public function testDelete()
    {
        // call delete view and assert values
        $getData = array(
            'id' => $this->channel2->getId()
        );
        $this->client->request('GET', self::$baseUrl ."{$this->channel2->getId()}/delete", $getData);
        $this->assertRedirectTo(self::$baseUrl .'index');

        // TODO : test if object not found
    }

}
