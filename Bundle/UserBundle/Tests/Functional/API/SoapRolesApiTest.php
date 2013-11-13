<?php

namespace Oro\Bundle\UserBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class SoapRolesApiTest extends WebTestCase
{
    /** Default value for role label */
    const DEFAULT_VALUE = 'ROLE_LABEL';

    /** @var Client */
    protected $client;

    public function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());
        $this->client->soap(
            "http://localhost/api/soap",
            array(
                'location' => 'http://localhost/api/soap',
                'soap_version' => SOAP_1_2
            )
        );
    }

    /**
     * @param string $request
     * @param array  $response
     *
     * @dataProvider requestsApi
     */
    public function testCreateRole($request, $response)
    {
        $this->markTestSkipped('BAP-1530 ACL API');
        if (is_null($request['label'])) {
            $request['label'] = self::DEFAULT_VALUE;
        }
        $id = $this->client->getSoap()->createRole($request);
        $this->assertInternalType('int', $id);
        $this->assertGreaterThan(0, $id);
    }

    /**
     * @param string $request
     * @param array  $response
     *
     * @dataProvider requestsApi
     * @depends testCreateRole
     */
    public function testUpdateRole($request, $response)
    {
        if (is_null($request['label'])) {
            $request['label'] = self::DEFAULT_VALUE;
        }
        //get role id
        $roleId =  $this->client->getSoap()->getRoleByName($request['label']);
        $roleId = ToolsAPI::classToArray($roleId);
        $request['label'] .= '_Updated';
        $result =  $this->client->getSoap()->updateRole($roleId['id'], $request);
        $result = ToolsAPI::classToArray($result);
        ToolsAPI::assertEqualsResponse($response, $result);
        $role =  $this->client->getSoap()->getRole($roleId['id']);
        $role = ToolsAPI::classToArray($role);
        $this->assertEquals($request['label'], $role['label']);
    }

    /**
     * @depends testUpdateRole
     * @return array
     */
    public function testGetRole()
    {
        //get roles
        $roles =  $this->client->getSoap()->getRoles();
        $roles = ToolsAPI::classToArray($roles);
        //filter roles
        $roles = array_filter(
            $roles['item'],
            function ($v) {
                return strpos($v['label'], '_Updated') !== false;
            }
        );
        $this->assertEquals(3, count($roles));

        return $roles;
    }

    /**
     * @depends testGetRole
     * @param array $roles
     */
    public function testDeleteRoles($roles)
    {
        //get roles
        foreach ($roles as $role) {
            $result =  $this->client->getSoap()->deleteRole($role['id']);
            $this->assertTrue($result);
        }
        $roles =  $this->client->getSoap()->getRoles();
        $roles = ToolsAPI::classToArray($roles);
        if (!empty($roles)) {
            $roles = array_filter(
                $roles['item'],
                function ($v) {
                    return strpos($v['label'], '_Updated') !== false;
                }
            );
        }
        $this->assertEmpty($roles);
    }

    /**
     * Data provider for REST API tests
     *
     * @return array
     */
    public function requestsApi()
    {
        return ToolsAPI::requestsApi(__DIR__ . DIRECTORY_SEPARATOR . 'RoleRequest');
    }
}
