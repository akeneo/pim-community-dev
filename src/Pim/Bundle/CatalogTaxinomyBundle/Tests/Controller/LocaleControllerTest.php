<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\Tests\Controller;
use Pim\Bundle\CatalogTaxinomyBundle\Entity\Locale;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LocaleControllerTest extends AbstractControllerTest
{

    /**
     * locale entity test
     * @var Locale
     */
    protected $locale1;

    /**
     * locale entity test
     * @var Locale
     */
    protected $locale2;

    /**
     * Base url of controller
     * @staticvar string
     */
    protected static $baseUrl = '/fr/catalogtaxinomy/locale/';

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->locale1 = $this->createLocale('test1');
        $this->locale2 = $this->createLocale('test2');

        $this->getEntityManager()->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        $this->getEntityManager()->remove($this->locale1);
        $this->getEntityManager()->remove($this->locale2);

        $this->getEntityManager()->flush();

        parent::tearDown();
    }

    /**
     * Create a locale entity
     * @param string $code
     *
     * @return \Pim\Bundle\CatalogTaxinomyBundle\Entity\Locale
     */
    protected function createLocale($code)
    {
        $locale = $this->getLocaleManager()->getNewEntityInstance();
        $locale->setCode($code);

        $this->getEntityManager()->persist($locale);

        return $locale;
    }

    /**
     * Get locale manager
     *
     * @return \Pim\Bundle\CatalogTaxinomyBundle\Model\LocaleManager
     */
    protected function getLocaleManager()
    {
        return $this->getContainer()->get('pim.catalog_taxinomy.locale_manager');
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
            'code' => 'locale-code'
        );

        // call create view and assert values
        $crawler = $this->client->request('POST', self::$baseUrl .'create', $postData);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('form'));

        // TODO : assert success message

        // assert wrong method (with GET parameters)
        $getData = array(
            'code' => 'locale-code-get'
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
        $form['pim_catalogtaxinomy_locale[code]'] = 'test5';
        $form['pim_catalogtaxinomy_locale[isDefault]'] = '1';
        // submit the form
        $crawler = $this->client->submit($form);
    }

    /**
     * test related class
     */
    public function testEdit()
    {
        // call edit view and assert values
        $crawler = $this->client->request('GET', self::$baseUrl ."{$this->locale2->getId()}/edit");
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
            'id'   => $this->locale2->getId(),
            'code' => 'locale-code'
        );

        // call update view and assert values
        $crawler = $this->client->request('POST', self::$baseUrl ."{$this->locale2->getId()}/update", $postData);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('form'));

        // TODO : assert message success

        // assert wrong method (with GET parameters)
        $getData = array(
            'id'   => $this->locale2->getId(),
            'code' => 'locale-code-get'
        );
        $this->client->request('GET', self::$baseUrl .'create', $getData);
        $this->assertEquals(405, $this->client->getResponse()->getStatusCode());

        // assert form call
        $crawler = $this->client->request('GET', self::$baseUrl ."{$this->locale2->getId()}/edit");
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('form'));
        // get form
        $form = $crawler->selectButton('edit-form-submit')->form();
        // set some values
        $timestamp = str_replace('.', '', microtime(true));
        $form['pim_catalogtaxinomy_locale[code]'] = 'test6';
        $form['pim_catalogtaxinomy_locale[isDefault]'] = '0';
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
            'id' => $this->locale2->getId()
        );
        $this->client->request('GET', self::$baseUrl ."{$this->locale2->getId()}/delete", $getData);
        $this->assertRedirectTo(self::$baseUrl .'index');
    }

}
