<?php

namespace Oro\Bundle\UserBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class RestApiUserTest extends WebTestCase
{

    public $client = null;

    public function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());
    }

    /**
     * @return array
     */
    public function testApiCreateRole()
    {
        $request = array(
            "role" => array (
                "role" => "new_role_" . mt_rand(),
                "label" => "new_label_" . mt_rand()
            )
        );
        $this->client->request('POST', 'http://localhost/api/rest/latest/role', $request);
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 201);
        $result = json_decode($result->getContent(), true);

        return $request;
    }

    /**
     * @param array $request
     * @depends testApiCreateRole
     *
     * @return int
    */
    public function testApiContainRole($request)
    {
        $this->client->request('GET', "http://localhost/api/rest/latest/roles");
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 200);
        $result = json_decode($result->getContent(), true);
        //compare result
        $roleId = $this->assertEqualsRoles($request, $result);

        return $roleId;
    }

    /**
     * @depends testApiContainRole
     * @param  int $roleId
     * @return int
     */
    public function testApiUpdateRole($roleId)
    {
        $requestUpdate = array(
            "role" => array (
                "role" => "~",
                "label" => "new_label_update"
            )
        );
        $this->client->request('PUT', 'http://localhost/api/rest/latest/roles/' . $roleId, $requestUpdate);
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 204);
        $this->client->request('GET', "http://localhost/api/rest/latest/roles");
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 200);
        $result = json_decode($result->getContent(), true);
        //compare result
        $roleId = $this->assertEqualsRoles($requestUpdate, $result);

        return $roleId;
    }

    /**
     * @depends testApiUpdateRole
     * @param int $roleId
     */
    public function testApiDeleteRole($roleId)
    {
        $this->client->request('DELETE', "http://localhost/api/rest/latest/roles/" . $roleId);
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 204);
    }

    /**
     * @return array
     */
    public function testApiCreateGroup()
    {
        $requestGroup = array(
            "group" => array (
                "name" => 'new_group_' . mt_rand(),
                "roles" => array(2)
            )
        );
        $this->client->request('POST', 'http://localhost/api/rest/latest/group', $requestGroup);
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 201);

        return $requestGroup;
    }

    /**
     * @depends testApiCreateGroup
     * @param  array $requestGroup
     * @return int
     */
    public function testApiContainGroup($requestGroup)
    {
        $this->client->request('GET', "http://localhost/api/rest/latest/groups");
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 200);
        $result = json_decode($result->getContent(), true);
        //compare result
        $groupId = $this->assertEqualsGroups($requestGroup, $result);

        return $groupId;
    }

    /**
     * @depends testApiContainGroup
     * @param  int $groupId
     * @return int
     */
    public function testApiUpdateGroup($groupId)
    {
        $requestUpdate = array(
            "group" => array (
                "name" => 'new_group_' . mt_rand(),
                "roles" => array(3)
            )
        );
        $this->client->request('PUT', 'http://localhost/api/rest/latest/groups/' . $groupId, $requestUpdate);
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 204);
        $this->client->request('GET', "http://localhost/api/rest/latest/groups");
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 200);
        $result = json_decode($result->getContent(), true);
        //compare result
        $groupId = $this->assertEqualsGroups($requestUpdate, $result);

        return $groupId;
    }

    /**
     * @depends testApiUpdateGroup
     * @param int
     */
    public function testApiDeleteGroup($groupId)
    {
        $this->client->request('DELETE', "http://localhost/api/rest/latest/groups/" . $groupId);
        $result = $this->client->getResponse();
        $this->assertJsonResponse($result, 204);
    }

    /**
     * Test API response status
     *
     * @param string $response
     * @param int    $statusCode
     */
    protected function assertJsonResponse($response, $statusCode = 201)
    {
        $this->assertEquals(
            $statusCode,
            $response->getStatusCode(),
            $response->getContent()
        );
    }

    /**
     * Check created role
     *
     * @return int
     * @param  array $result
     * @param  array $request
     */
    protected function assertEqualsRoles($request, $result)
    {
        $flag = 1;
        foreach ($result as $key => $object) {
            foreach ($request as $role) {
                if ($role['label'] == $result[$key]['label']) {
                    $flag = 0;
                    $roleId = $result[$key]['id'];
                    break 2;
                }
            }
        }
        $this->assertEquals(0, $flag);

        return $roleId;
    }

    /**
     * Check created group
     *
     * @return int
     * @param  array $result
     * @param  array $requestGroup
     */
    protected function assertEqualsGroups($requestGroup, $result)
    {
        $groupId = 0;
        $flag = 1;
        foreach ($result as $key => $object) {
            foreach ($requestGroup as $group) {
                if ($group['name'] == $result[$key]['name']) {
                    $flag = 0;
                    $groupId = $result[$key]['id'];
                    break 2;
                }
            }
        }
        $this->assertEquals(0, $flag);

        return $groupId;
    }
}
