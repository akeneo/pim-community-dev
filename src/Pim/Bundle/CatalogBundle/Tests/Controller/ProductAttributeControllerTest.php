<?php
namespace Pim\Bundle\CatalogBundle\Tests\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductAttributeControllerTest extends WebTestCase
{
    /**
     * test related action
     */
    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/fr/catalog/productattribute/index');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('div.grid'));
    }

    /**
     * test related action
     */
    public function testNew()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/fr/catalog/productattribute/new');
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
        $crawler = $client->request('GET', '/fr/catalog/productattribute/new');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('form'));
        // get form
        $form = $crawler->selectButton('edit-form-submit')->form();
        // set some values
        $timestamp = str_replace('.', '', microtime(true));
        $form['pim_catalogbundle_productattributetype[code]'] = 'MyCode'.$timestamp;
        $form['pim_catalogbundle_productattributetype[title]'] = 'Title';
        $form['pim_catalogbundle_productattributetype[type]'] = 'string';
        $toSelect = array('scope', 'searchable', 'translatable', 'uniqueValue', 'valueRequired');
        foreach ($toSelect as $field) {
            $form['pim_catalogbundle_productattributetype['.$field.']'] = '0';
        }
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
        $attribute = $container->get('pim.catalog.product_manager')->getAttributeRepository()->findAll()->getSingleResult();
        $this->assertNotNull($attribute);
        // get page
        $crawler = $client->request('GET', "/fr/catalog/productattribute/{$attribute->getId()}/edit");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('form'));
        // get form
        $form = $crawler->selectButton('edit-form-submit')->form();
        // set some values
        $timestamp = str_replace('.', '', microtime(true));
        $form['pim_catalogbundle_productattributetype[title]'] = 'New title '.$timestamp;
        // submit the form
        $crawler = $client->submit($form);
    }

    /**
     * test related action
     * TODO : cause problem due to missing cascade on ODM
    public function testDelete()
    {
        // get first attribute
        $client = static::createClient();
        $container = $client->getContainer();
        $attribute = $container->get('pim.catalog.product_manager')->getAttributeRepository()->findAll()->getSingleResult();
        $this->assertNotNull($attribute);
        // delete call
        $crawler = $client->request('GET', "/fr/catalog/productattribute/{$attribute->getId()}/delete");
        // redirect on index
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }*/

}
