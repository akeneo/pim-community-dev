<?php

namespace Oro\Bundle\DataAudit\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;
use Symfony\Component\DomCrawler\Form;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class ControllersTest extends WebTestCase
{
    protected $userData = array(
        'username' => 'testAdmin',
        'email' => 'test@test.com',
        'firstName' => 'FirstNameAudit',
        'lastName' => 'LastNameAudit',
        'birthday' => '07/01/2013',
        'enabled' => 1,
        'roles' => 'Superadmin',
        'groups' => 'Sales',
        'company' => 'company',
        'gender' => 'Male'
    );
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

    public function prepareFixture()
    {
        $crawler = $this->client->request('GET', $this->client->generate('oro_user_create'));
        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_user_user_form[enabled]'] = $this->userData['enabled'];
        $form['oro_user_user_form[username]'] = $this->userData['username'];
        $form['oro_user_user_form[plainPassword][first]'] = 'password';
        $form['oro_user_user_form[plainPassword][second]'] = 'password';
        $form['oro_user_user_form[firstName]'] = $this->userData['firstName'];
        $form['oro_user_user_form[lastName]'] = $this->userData['lastName'];
        $form['oro_user_user_form[birthday]'] = $this->userData['birthday'];
        $form['oro_user_user_form[email]'] = $this->userData['email'];
        $form['oro_user_user_form[groups][1]'] = 2;
        $form['oro_user_user_form[rolesCollection][2]'] = 4;
        $form['oro_user_user_form[values][company][varchar]'] = $this->userData['company'];

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("User successfully saved", $crawler->html());
    }

    public function testIndex()
    {
        $this->client->request('GET', $this->client->generate('oro_dataaudit_index'));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
    }

    public function testHistory()
    {
        $this->prepareFixture();
        $this->client->request(
            'GET',
            $this->client->generate('oro_dataaudit_index', array('_format' =>'json')),
            array(
                'audit[_filter][objectName][type]' => null,
                'audit[_filter][objectName][value]' => $this->userData['username'],
                'audit[_pager][_page]' => 1,
                'audit[_pager][_per_page]' => 10,
                'audit[_sort_by][action]' => 'ASC')
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);
        $this->client->request(
            'GET',
            $this->client->generate(
                'oro_dataaudit_history',
                array(
                    'entity' => str_replace('\\', '_', $result['objectClass']),
                    'id' => $result['objectId'],
                    '_format' =>'json'
                )
            )
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);

        $result['old'] = strip_tags(preg_replace('/(<!--(.*)-->)|(\h)/Uis', '', $result['old']));
        $count = 0;
        do {
            $result['old'] = strip_tags(preg_replace('/\n{2,}/Uis', "\n", $result['old'], -1, $count));
        } while ($count > 0);
        $result['new'] = strip_tags(preg_replace('/(<!--(.*)-->)|(\h)/Uis', '', $result['new']));
        $count = 0;
        do {
            $result['new'] = strip_tags(preg_replace('/\n{2,}/Uis', "\n", $result['new'], -1, $count));
        } while ($count > 0);
        $result['old'] = explode("\n", trim($result['old'], "\n"));
        $result['new'] = explode("\n", trim($result['new'], "\n"));
        foreach ($result['old'] as $auditRecord) {
            $auditValue = explode(':', $auditRecord);
            $this->assertEquals('', $auditValue[1]);
        }

        foreach ($result['new'] as $auditRecord) {
            $auditValue = explode(':', $auditRecord);
            $this->assertEquals($this->userData[$auditValue[0]], $auditValue[1]);
        }

        $this->assertEquals('John Doe  - admin@example.com', $result['author']);

    }
}
