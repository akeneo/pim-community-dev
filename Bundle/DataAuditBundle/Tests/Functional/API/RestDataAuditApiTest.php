<?php

namespace Oro\Bundle\DataAuditBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class RestDataAuditApiTest extends WebTestCase
{

    /** @var Client */
    protected $client;

    public function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());
    }

    /**
     * @return array
     */
    public function testPreconditions()
    {
        // create users
        $request = array(
            "user" => array (
                "username" => 'user_' . mt_rand(),
                "email" => 'test_'  . mt_rand() . '@test.com',
                "enabled" => '1',
                "plainPassword" => '1231231q',
                "namePrefix" => "Mr",
                "firstName" => "firstName",
                "middleName" => "middleName",
                "lastName" => "lastName",
                "nameSuffix" => "Sn.",
                "rolesCollection" => array("2"),
                "owner" => "1",
            )
        );

        $this->client->request('POST', $this->client->generate('oro_api_post_user'), $request);
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
        $this->client->request('GET', $this->client->generate('oro_api_get_audits'));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        $resultActual = reset($result);
        $this->assertEquals('create', $resultActual['action']);
        $this->assertEquals('Oro\Bundle\UserBundle\Entity\User', $resultActual['object_class']);
        $this->assertEquals($response['user']['username'], $resultActual['object_name']);
        $this->assertEquals('admin', $resultActual['username']);
        $this->assertEquals($response['user']['username'], $resultActual['data']['username']['new']);
        $this->assertEquals($response['user']['email'], $resultActual['data']['email']['new']);
        $this->assertEquals($response['user']['enabled'], $resultActual['data']['enabled']['new']);
        $this->assertEquals('User', $resultActual['data']['roles']['new']);

        return $result;
    }

    /**
     * @param  array $response
     * @depends testGetAudits
     */
    public function testGetAudit($response)
    {
        foreach ($response as $audit) {
            $this->client->request('GET', $this->client->generate('oro_api_get_audit', array('id' => $audit['id'])));
            $result = $this->client->getResponse();
            ToolsAPI::assertJsonResponse($result, 200);
            $result = ToolsAPI::jsonToArray($result->getContent());
            unset($result['loggedAt']);
            unset($audit['loggedAt']);
            $this->assertEquals($audit, $result);
        }
    }
}
