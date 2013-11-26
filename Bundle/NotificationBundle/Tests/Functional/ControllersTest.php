<?php

namespace Oro\Bundle\NotificationBundle\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class ControllersTest extends WebTestCase
{
    const ENTITY_NAME = 'Oro\Bundle\UserBundle\Entity\User';

    protected $eventUpdate;
    protected $eventCreate;
    protected $templateUpdate;

    /**
     * @var Client
     */
    protected $client;

    public function setUp()
    {
        $this->client = static::createClient(
            array(),
            array_merge(ToolsAPI::generateBasicHeader(), array('HTTP_X-CSRF-Header' => 1))
        );
    }

    protected function prepareData()
    {
        $notificationManager = $this->client->getContainer()->get('doctrine');
        $this->eventUpdate  = $notificationManager
            ->getRepository('OroNotificationBundle:Event')
            ->findOneBy(array('name' => 'oro.notification.event.entity_post_update'));

        $this->eventCreate  = $notificationManager
            ->getRepository('OroNotificationBundle:Event')
            ->findOneBy(array('name' => 'oro.notification.event.entity_post_persist'));

        $this->templateUpdate  = $notificationManager
            ->getRepository('OroEmailBundle:EmailTemplate')
            ->findOneBy(array('entityName' => self::ENTITY_NAME));
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->client->generate('oro_notification_emailnotification_index'));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
    }

    /**
     * @depends testIndex
     */
    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->client->generate('oro_notification_emailnotification_create'));

        // prepare data for next tests
        $this->prepareData();

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['emailnotification[entityName]'] = 'Oro\Bundle\UserBundle\Entity\User';
        $form['emailnotification[event]'] = $this->eventUpdate->getId();
        $doc = new \DOMDocument("1.0");
        $doc->loadHTML(
            '<select required="required" name="emailnotification[template]" id="emailnotification_template" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="' . $this->templateUpdate->getId() . '">EmailBundle:' . $this->templateUpdate->getName() . '</option> </select>'
        );

        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['emailnotification[template]'] = $this->templateUpdate->getId();
        $form['emailnotification[recipientList][users]'] = '1';
        $form['emailnotification[recipientList][groups][0]'] = '1';
        $form['emailnotification[recipientList][email]'] = 'admin@example.com';
        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("Email notification rule saved", $crawler->html());
    }

    /**
     * @depends testCreate
     */
    public function testUpdate()
    {
        $result = ToolsAPI::getEntityGrid(
            $this->client,
            'email-notification-grid',
            array(
                'email-notification-grid[_pager][_page]' => 1,
                'email-notification-grid[_pager][_per_page]' => 1
            )
        );

        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);

        $crawler = $this->client->request(
            'GET',
            $this->client->generate('oro_notification_emailnotification_update', array('id' => $result['id']))
        );

        // prepare data for next tests
        $this->prepareData();

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['emailnotification[entityName]'] = 'Oro\Bundle\UserBundle\Entity\User';
        $form['emailnotification[event]'] = $this->eventCreate->getId();
        $doc = new \DOMDocument("1.0");
        $doc->loadHTML(
            '<select required="required" name="emailnotification[template]" id="emailnotification_template" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="' . $this->templateUpdate->getId() . '">EmailBundle:' . $this->templateUpdate->getName() . '</option> </select>'
        );

        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['emailnotification[template]'] = $this->templateUpdate->getId();
        $form['emailnotification[recipientList][users]'] = '1';
        $form['emailnotification[recipientList][groups][0]'] = '1';
        $form['emailnotification[recipientList][email]'] = 'admin@example.com';
        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("Email notification rule saved", $crawler->html());
    }

    /**
     * @depends testCreate
     */
    public function testDelete()
    {

        $result = ToolsAPI::getEntityGrid(
            $this->client,
            'email-notification-grid',
            array(
                'email-notification-grid[_pager][_page]' => 1,
                'email-notification-grid[_pager][_per_page]' => 1
            )
        );

        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);

        $this->client->request(
            'DELETE',
            $this->client->generate('oro_api_delete_emailnotication', array('id' => $result['id']))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);
    }
}
