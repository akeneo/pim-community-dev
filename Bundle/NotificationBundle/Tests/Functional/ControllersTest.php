<?php

namespace Oro\Bundle\NotificationBundle\Tests\Functional;

use Symfony\Component\DomCrawler\Form;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;
use \Oro\Bundle\EmailBundle\Entity\EmailTemplate;

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
        $this->client = static::createClient(
            array(),
            array_merge(ToolsAPI::generateBasicHeader(), array('HTTP_X-CSRF-Header' => 1))
        );
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->client->generate('oro_notification_emailnotification_index'));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
    }

    public function testCreate()
    {
        $crawler = $this->client->request('GET', $this->client->generate('oro_notification_emailnotification_create'));

        $emailTemplate = $this->getEmailTemplate('EmailBundle:update_user');

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['emailnotification[entityName]'] = 'Oro\Bundle\UserBundle\Entity\User';
        $form['emailnotification[event]'] = '3';
        $doc = new \DOMDocument("1.0");
        $doc->loadHTML(
            '<select required="required" name="emailnotification[template]" id="emailnotification_template" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="' . $emailTemplate->getId() . '">' . $emailTemplate->getName() . '</option> </select>'
        );

        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['emailnotification[template]'] = $emailTemplate->getId();
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
        $this->client->request(
            'GET',
            $this->client->generate('oro_notification_emailnotification_index', array('_format' =>'json')),
            array('emailnotification[_pager][_page]' => 1, 'emailnotification[_pager][_per_page]' => 1)
        );

        $emailTemplate = $this->getEmailTemplate('EmailBundle:update_user');

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);

        $crawler = $this->client->request(
            'GET',
            $this->client->generate('oro_notification_emailnotification_update', array('id' => $result['id']))
        );
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['emailnotification[entityName]'] = 'Oro\Bundle\UserBundle\Entity\User';
        $form['emailnotification[event]'] = '3';
        $doc = new \DOMDocument("1.0");
        $doc->loadHTML(
            '<select required="required" name="emailnotification[template]" id="emailnotification_template" ' .
            'tabindex="-1" class="select2-offscreen"> ' .
            '<option value="" selected="selected"></option> ' .
            '<option value="' . $emailTemplate->getId() . '">' . $emailTemplate->getName() . '</option> </select>'
        );

        $field = new ChoiceFormField($doc->getElementsByTagName('select')->item(0));
        $form->set($field);
        $form['emailnotification[template]'] = $emailTemplate->getId();
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
        $this->client->request(
            'GET',
            $this->client->generate('oro_notification_emailnotification_index', array('_format' =>'json')),
            array('emailnotification[_pager][_page]' => 1, 'emailnotification[_pager][_per_page]' => 1)
        );
        $result = $this->client->getResponse();
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

    /**
     * @param string $name
     * @return EmailTemplate
     * @throws \LogicException
     */
    protected function getEmailTemplate($name)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->client->getKernel()->getContainer()->get('doctrine.orm.entity_manager');
        $emailTemplate = $entityManager->getRepository('OroEmailBundle:EmailTemplate')->findOneBy(
            array('name' => $name)
        );

        if (!$emailTemplate) {
            throw new \LogicException(sprintf('Cant\'t find template with name %s', $name));
        }

        return $emailTemplate;
    }
}
