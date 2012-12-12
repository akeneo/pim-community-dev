<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\Tests\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class LocaleControllerTest extends WebTestCase
{
    /**
     * Base url of controller
     * @staticvar string
     */
    protected static $baseUrl = '/fr/catalogtaxinomy/locale/';

    /**
     * test related action
     */
    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', self::$baseUrl .'index');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('div.grid'));
    }

    /**
     * test related action
     */
    public function testNew()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', self::$baseUrl .'new');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('form'));
    }

    /**
     * test related action
     */
    public function testCreate()
    {
        // get page
        $client = static::createClient();
        $crawler = $client->request('GET', self::$baseUrl .'new');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('form'));
        // get form
        $form = $crawler->selectButton('edit-form-submit')->form();
        // set some values
        $timestamp = str_replace('.', '', microtime(true));
        $form['pim_catalogtaxinomy_locale[code]'] = 'it_IT';
        // submit the form
        $crawler = $client->submit($form);
    }

    /**
     * test related action
     */
    public function testUpdate()
    {
        // get first attribute
        $client = static::createClient();
        $container = $client->getContainer();
        $attribute = $container->get('doctrine.orm.entity_manager')->getRepository('PimCatalogTaxinomyBundle:Locale')->findOneBy(array());
        $this->assertNotNull($attribute);
        // get page
        $crawler = $client->request('GET', self::$baseUrl ."{$attribute->getId()}/edit");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('form'));
        // get form
        $form = $crawler->selectButton('edit-form-submit')->form();
        // set some values
        $timestamp = str_replace('.', '', microtime(true));
        $form['pim_catalogtaxinomy_locale[code]'] = 'en_US';
        // submit the form
        $crawler = $client->submit($form);
    }

}
