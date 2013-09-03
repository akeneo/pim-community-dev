<?php

namespace Oro\Bundle\EmailBundle\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class ControllersTest extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    public function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateBasicHeader());
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->client->generate('oro_email_emailtemplate_index'));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
    }

    public function testCreate()
    {
        $this->markTestIncomplete('Skipped due to issue with dynamic form loading');
        $crawler = $this->client->request('GET', $this->client->generate('oro_email_emailtemplate_create'));
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML($crawler->html());
        $dom->getElementById('oro_email_emailtemplate');
        $form = $crawler->filterXPath("//form[@name='oro_email_emailtemplate']");

        $form = $crawler->selectButton('Save and Close')->form();
        $fields = $form->all();
        $form['oro_email_emailtemplate[entityName]'] = 'Oro\Bundle\UserBundle\Entity\User';
        $form['oro_email_emailtemplate[name]'] = 'User Template';
        $form['oro_email_emailtemplate[translations][defaultLocale][en][content]'] = 'Content template';
        $form['oro_email_emailtemplate[translations][defaultLocale][en][subject]'] = 'Subject';
        $form['oro_email_emailtemplate[type]'] = 'html';

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, '');
        $this->assertContains("Template sucessfully saved", $crawler->html());
    }
}
