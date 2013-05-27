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

    protected $client = null;

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
                "label" => $roleName
            )
        );
        $this->client->request('POST', 'http://localhost/api/rest/latest/role', $request);
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
        $this->client->request('GET', 'http://localhost/api/rest/latest/roles/' . $request['role']['role'] . '/byname');
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
    }

    /**
     * @depends testApiCreateRole
     * @param array $request
     * @return int $roleId
     */
    public function testApiGetRoleById($request)
    {
        $this->client->request('GET', 'http://localhost/api/rest/latest/roles');
        $result = $this->client->getResponse();
        $result = json_decode($result->getContent(), true);
        foreach ($result as $role) {
            if ($role['role'] == strtoupper($request['role']['label'])) {
                $roleId = $role['id'];
                break;
            }
        }
        $this->client->request('GET', 'http://localhost/api/rest/latest/roles' .'/'. $roleId);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        return $roleId;
    }

    /**
     * @depends testApiGetRoleById
     * @depends testApiCreateRole
     * @param int $roleId
     * @param array $request
     */
    public function testApiUpdateRole($roleId, $request)
    {
        $request['role']['label'] .= '_Update';
        $request['role']['role'] .= '_Update';
        $this->client->request('PUT', 'http://localhost/api/rest/latest/roles' . '/' . $roleId, $request);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);
        $this->client->request('GET', 'http://localhost/api/rest/latest/roles' .'/'. $roleId);
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
        $this->client->request('DELETE', 'http://localhost/api/rest/latest/roles' .'/'. $roleId);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);
        $this->client->request('GET', 'http://localhost/api/rest/latest/roles' . '/' . $roleId);
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 404);
    }
}
