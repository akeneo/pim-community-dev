<?php

namespace Oro\Bundle\UserBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class RestApiAclTest extends WebTestCase
{
    const TEST_ROLE = 'ROLE_SUPER_ADMIN';
    const TEST_EDIT_ROLE = 'ROLE_USER';
    /**
     * @var Client
     */
    protected $client = null;

    public function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());
    }

    /**
     * @return array
     */
    public function testGetAcls()
    {
        $this->client->request('GET', 'http://localhost/api/rest/latest/acls');
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        sort($result);
        return $result;
    }

    /**
     * @param $acls
     * @depends testGetAcls
     */
    public function testGetAcl($acls)
    {
        foreach ($acls as $acl) {
            $this->client->request('GET', 'http://localhost/api/rest/latest/acls/' . $acl);
            $result = $this->client->getResponse();
            ToolsAPI::assertJsonResponse($result, 200);
        }
    }

    /**
     * @param $acls
     * @return array
     * @depends testGetAcls
     */
    public function testGetRoleAcl($acls)
    {
        $this->client->request('GET', 'http://localhost/api/rest/latest/roles/' . self::TEST_ROLE . '/byname');
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $roleId = ToolsAPI::jsonToArray($result->getContent());
        $this->client->request('GET', "http://localhost/api/rest/latest/roles/{$roleId['id']}/acl");
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        sort($result);
        $this->assertEquals($acls, $result);
        return $result;
    }

    /**
     * @param $acls
     */
    public function testGetUserAcl($acls)
    {
        $this->client->request('GET', 'http://localhost/api/rest/latest/user/filter', array('username' => 'admin'));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        $this->client->request('GET', "http://localhost/api/rest/latest/users/{$result['id']}/acl");
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        sort($result);
        $this->assertEquals($acls, $result);
    }

    public function testRemoveAclFromRole()
    {
        $this->markTestSkipped('CRM-182');
        $this->client->request('GET', 'http://localhost/api/rest/latest/roles/' . self::TEST_EDIT_ROLE . '/byname');
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $roleId = ToolsAPI::jsonToArray($result->getContent());

        $this->client->request('GET', "http://localhost/api/rest/latest/roles/{$roleId['id']}/acl");
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $expectedAcl = ToolsAPI::jsonToArray($result->getContent());
        sort($expectedAcl);
        unset($expectedAcl[array_search('oro_address', $expectedAcl)]);

        $this->client->request('DELETE', "http://localhost/api/rest/latest/roles/{$roleId['id']}/acls/oro_address");
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);

        $this->client->request('GET', "http://localhost/api/rest/latest/roles/{$roleId['id']}/acl");
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $actualAcl = ToolsAPI::jsonToArray($result->getContent());
        sort($actualAcl);

        $this->assertEquals($expectedAcl, $actualAcl);
    }

    /**
     * @depends testRemoveAclFromRole
     */
    public function testAddAclToRole()
    {
        $this->client->request('GET', 'http://localhost/api/rest/latest/roles/' . self::TEST_EDIT_ROLE . '/byname');
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $roleId = ToolsAPI::jsonToArray($result->getContent());

        $this->client->request('POST', "http://localhost/api/rest/latest/roles/{$roleId['id']}/acls/oro_address");
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
    }

    public function testRemoveAclsFromRole()
    {
        $this->markTestSkipped('CRM-182');
    }

    /**
     * @depends testRemoveAclsFromRole
     */
    public function testAddAclsToRole()
    {
        $this->markTestSkipped('CRM-182');
    }
}
