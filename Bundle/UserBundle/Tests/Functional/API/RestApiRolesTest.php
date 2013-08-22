<?php

namespace Oro\Bundle\UserBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class RestApiRolesTest extends WebTestCase
{
    /** @var Client  */
    protected $client;

    public function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());
    }

    /**
     * @return array $request
     */
    public function testApiCreateRole()
    {
        $roleName = 'Role_'.mt_rand(100, 500);
        $request = array(
            "role" => array(
                "role" => $roleName,
                "label" => $roleName,
                "owner" => 1
            )
        );
        $this->client->request('POST', $this->client->generate('oro_api_post_role'), $request);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 201);

        return $request;
    }

    /**
     * @depends testApiCreateRole
     * @param array $request
     */
    public function testApiGetRoleByName($request)
    {
        $this->client->request(
            'GET',
            $this->client->generate('oro_api_get_role_byname', array('name' => $request['role']['role']))
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
    }

    /**
     * @depends testApiCreateRole
     * @param  array $request
     * @return int   $roleId
     */
    public function testApiGetRoleById($request)
    {
        $this->client->request('GET', $this->client->generate('oro_api_get_roles'));
        $result = $this->client->getResponse();
        $result = json_decode($result->getContent(), true);
        foreach ($result as $role) {
            if ($role['role'] == strtoupper($request['role']['label'])) {
                $roleId = $role['id'];
                break;
            }
        }
        $this->client->request('GET', $this->client->generate('oro_api_get_role', array('id' => $roleId)));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);

        return $roleId;
    }

    /**
     * @depends testApiGetRoleById
     * @depends testApiCreateRole
     * @param int   $roleId
     * @param array $request
     */
    public function testApiUpdateRole($roleId, $request)
    {
        $request['role']['label'] .= '_Update';
        $request['role']['role'] .= '_Update';
        $this->client->request('PUT', $this->client->generate('oro_api_put_role', array('id' => $roleId)), $request);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);
        $this->client->request('GET', $this->client->generate('oro_api_get_role', array('id' => $roleId)));
        $result = $this->client->getResponse();
        $result = json_decode($result->getContent(), true);
        $this->assertEquals($result['label'], $request['role']['label'], 'Role does not updated');
    }

    /**
     * @depends testApiGetRoleById
     * @param $roleId
     */
    public function testApiDeleteRole($roleId)
    {
        $this->client->request('DELETE', $this->client->generate('oro_api_delete_role', array('id' => $roleId)));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);
        $this->client->request('GET', $this->client->generate('oro_api_get_role', array('id' => $roleId)));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 404);
    }
}
