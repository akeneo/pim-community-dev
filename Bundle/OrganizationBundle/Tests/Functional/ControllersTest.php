<?php

namespace Oro\Bundle\OrganizationBundle\Tests\Functional;

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
        $this->client->request('GET', $this->client->generate('oro_business_unit_index'));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
    }

    /**
     * @return array
     */
    protected function getUser()
    {
        $request = array(
            "user" => array (
                "username" => 'user_' . mt_rand(),
                "email" => 'test_'  . mt_rand() . '@test.com',
                "enabled" => '1',
                "plainPassword" => '1231231q',
                "firstName" => "firstName",
                "lastName" => "lastName",
                "rolesCollection" => array("3"),
                "owner" => "1",
            )
        );
        $this->client->request('POST', $this->client->generate('oro_api_post_user'), $request);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 201);
        $result = ToolsAPI::jsonToArray($result->getContent());
        $result['request'] = $request;
        return $result;
    }

    public function testCreate()
    {
        $user = $this->getUser();
        $crawler = $this->client->request('GET', $this->client->generate('oro_business_unit_create'));
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_business_unit_form[name]'] = 'testBU';
        $form['oro_business_unit_form[organization]'] = 1;
        $form['oro_business_unit_form[appendUsers]'] = $user['id'];
        $form['oro_business_unit_form[email]'] = 'test@test.com';
        $form['oro_business_unit_form[phone]'] = '123-123-123';
        $form['oro_business_unit_form[fax]'] = '321-321-321';
        $form['oro_business_unit_form[owner]'] = 1;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("Business Unit saved", $crawler->html());

        return $user;
    }

    /**
     * @depends testCreate
     * @return string
     */
    public function testUpdate()
    {
        $result = ToolsAPI::getEntityGrid(
            $this->client,
            'business-unit-grid',
            array(
                'business-unit-grid[_filter][name][value]' => 'testBU'
            )
        );

        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);

        $crawler = $this->client->request(
            'GET',
            $this->client->generate('oro_business_unit_update', array('id' => $result['id']))
        );
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $form['oro_business_unit_form[name]'] = 'testBU_Updated';
        $form['oro_business_unit_form[owner]'] = 1;

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("Business Unit saved", $crawler->html());

        $result = ToolsAPI::getEntityGrid(
            $this->client,
            'business-unit-grid',
            array(
                'business-unit-grid[_filter][name][value]' => 'testBU_Updated'
            )
        );

        ToolsAPI::assertJsonResponse($result, 200);

        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);
        return $result['id'];
    }

    /**
     * @depends testUpdate
     * @param string $id
     */
    public function testView($id)
    {

        $crawler = $this->client->request(
            'GET',
            $this->client->generate('oro_business_unit_view', array('id' => $id))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, 'text/html; charset=UTF-8');
        $this->assertContains("testBU_Updated - Business Units - Users Management - System", $crawler->html());
    }

    /**
     * @depends testUpdate
     * @depends testCreate
     * @param string $id
     * @param array $user
     */
    public function testViewUsers($id, $user)
    {
        $result = ToolsAPI::getEntityGrid(
            $this->client,
            'bu-view-users-grid',
            array(
                'bu-view-users-grid[business_unit_id]' => $id
            )
        );

        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        $result = reset($result['data']);

        $this->assertEquals($user['request']['user']['username'], $result['username']);
    }
}
