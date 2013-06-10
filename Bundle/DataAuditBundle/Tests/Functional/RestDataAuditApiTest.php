<?php

namespace Oro\Bundle\DataAuditBundle\Tests;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class RestDataAuditApiTest extends WebTestCase
{

    protected $client = null;

    public function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());
    }

    /**
     * @return array
     */
    public function testPreconditions()
    {
        //create users
        $request = array(
            "user" => array (
                "username" => 'user_' . mt_rand(),
                "email" => 'test_'  . mt_rand() . '@test.com',
                "enabled" => '1',
                "plainPassword" => '1231231q',
                "firstName" => "firstName",
                "lastName" => "lastName",
                "rolesCollection" => array("1")
            )
        );

        $this->client->request('POST', 'http://localhost/api/rest/latest/user', $request);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 201);

        return $request;
    }

    /**
     * @param $response
     * @return array
     * @depends testPreconditions
     */
    public function testGetAudits($response)
    {
        $this->client->request('GET', 'http://localhost/api/rest/latest/audits');
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        return $result;
    }

    /**
     * @param array $response
     * @return array
     * @depends testGetAudits
     */
    public function testGetAudit($response)
    {
        $this->client->request('GET', 'http://localhost/api/rest/latest/audits/' . $response[0]['id']);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        return $result;
    }

    /**
     * @param array $response
     * @depends testGetAudit
     */
    public function testDeleteAudit($response)
    {
        $this->client->request('DELETE', 'http://localhost/api/rest/latest/audits/' . $response['id']);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);
        $this->client->request('GET', 'http://localhost/api/rest/latest/audits/' . $response['id']);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 404);
    }
}
