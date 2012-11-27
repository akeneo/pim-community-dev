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
class ChannelControllerTest extends WebTestCase
{
    /**
     * test related action
     */
    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/fr/catalogtaxinomy/channel/index');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('div.grid'));
    }

    /**
     * test related action
     */
    public function testNew()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/fr/catalogtaxinomy/channel/new');
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
        $crawler = $client->request('GET', '/fr/catalogtaxinomy/channel/new');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('form'));
        // get form
        $form = $crawler->selectButton('edit-form-submit')->form();
        // set some values
        $timestamp = str_replace('.', '', microtime(true));
        $form['pim_catalogtaxinomy_channel[code]'] = 'My code '.$timestamp;
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
        $attribute = $container->get('doctrine.orm.entity_manager')->getRepository('PimCatalogTaxinomyBundle:Channel')->findOneBy(array());
        $this->assertNotNull($attribute);
        // get page
        $crawler = $client->request('GET', "/fr/catalogtaxinomy/channel/{$attribute->getId()}/edit");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('form'));
        // get form
        $form = $crawler->selectButton('edit-form-submit')->form();
        // set some values
        $timestamp = str_replace('.', '', microtime(true));
        $form['pim_catalogtaxinomy_channel[code]'] = 'New code '.$timestamp;
        // submit the form
        $crawler = $client->submit($form);
    }

}
