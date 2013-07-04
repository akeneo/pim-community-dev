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
        $this->client->request('GET', $this->client->generate('oro_api_get_acls'));
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
            $this->client->request('GET', $this->client->generate('oro_api_get_acl', array('id' => $acl)));
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
        $this->client->request('GET', $this->client->generate('oro_api_get_role_byname', array('name' => self::TEST_ROLE)));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $roleId = ToolsAPI::jsonToArray($result->getContent());
        $this->client->request('GET', $this->client->generate('oro_api_get_role_acl', array('id' => $roleId['id'])));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        sort($result);
        $this->assertEquals($acls, $result);
        return $result;
    }

    /**
     * @param $acls
     * @depends testGetAcls
     */
    public function testGetUserAcl($acls)
    {
        $this->client->request('GET', $this->client->generate('oro_api_get_user_filter', array('username' => 'admin')));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        $this->client->request('GET', $this->client->generate('oro_api_get_user_acl', array('id' => $result['id'])));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $result = ToolsAPI::jsonToArray($result->getContent());
        sort($result);
        $this->assertEquals($acls, $result);
    }

    public function testRemoveAclFromRole()
    {
        $this->client->request('GET', $this->client->generate('oro_api_get_role_byname', array('name' => self::TEST_EDIT_ROLE)));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $roleId = ToolsAPI::jsonToArray($result->getContent());

        $this->client->request('GET', $this->client->generate('oro_api_get_role_acl', array('id' => $roleId['id'])));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $expectedAcl = ToolsAPI::jsonToArray($result->getContent());
        sort($expectedAcl);
        $tmpExpectedAcl = $expectedAcl;
        foreach ($expectedAcl as $key => $val) {
            if (preg_match('/oro_address*/', $val) || $val == 'root') { // root resource will be deleted after any resource delete
                unset($expectedAcl[ $key ]);
            }
        }
        sort($expectedAcl);

        $this->client->request('DELETE', $this->client->generate('oro_api_delete_role_acl', array('id' => $roleId['id'], 'resource' => 'oro_address')));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);

        $this->client->request('GET', $this->client->generate('oro_api_get_role_acl', array('id' => $roleId['id'])));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $actualAcl = ToolsAPI::jsonToArray($result->getContent());
        sort($actualAcl);

        $this->assertEquals($expectedAcl, $actualAcl);
        return $tmpExpectedAcl;
    }

    /**
     * @depends testRemoveAclFromRole
     * @param $expectedAcl
     * @return array
     */
    public function testAddAclToRole($expectedAcl)
    {
        $this->client->request('GET', $this->client->generate('oro_api_get_role_byname', array('name' => self::TEST_EDIT_ROLE)));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $roleId = ToolsAPI::jsonToArray($result->getContent());

        $this->client->request('POST', $this->client->generate('oro_api_post_role_acl', array('id' => $roleId['id'], 'resource' => 'oro_address')));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);

        $this->client->request('GET', $this->client->generate('oro_api_get_role_acl', array('id' => $roleId['id'])));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $actualAcl = ToolsAPI::jsonToArray($result->getContent());
        sort($actualAcl);

        foreach ($expectedAcl as $key => $val) {
            if ($val == 'root') { // root resource will be deleted after any resource delete
                unset($expectedAcl[ $key ]);
            }
        }
        sort($expectedAcl);

        $this->assertEquals($expectedAcl, $actualAcl);

        return $actualAcl;
    }

    /**
     * @depends testAddAclToRole
     */
    public function testRemoveAclsFromRole($expectedAcl)
    {
        $this->markTestSkipped('BAP-1058');
        $this->client->request('GET', $this->client->generate('oro_api_get_role_byname', array('name' => self::TEST_EDIT_ROLE)));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $roleId = ToolsAPI::jsonToArray($result->getContent());
        $tmpExpectedAcl = $expectedAcl;
        foreach ($expectedAcl as $key => $val) {
            if (preg_match('/oro_address*/', $val) || $val == 'root'
                || in_array(
                    $val,
                    array(
                    'oro_security', 'oro_login', 'oro_login_check', 'oro_logout', 'oro_reset_check_email',
                    'oro_reset_controller', 'oro_reset_password', 'oro_reset_request', 'oro_reset_send_mail')
                )) { // root resource will be deleted after any resource delete
                unset($expectedAcl[ $key ]);
            }
        }
        sort($expectedAcl);
        $this->client->request(
            'DELETE',
            $this->client->generate('oro_api_delete_role_acl_array', array('id' => $roleId['id'])),
            array('resources' => array('oro_security','oro_address'))
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);

        $this->client->request('GET', $this->client->generate('oro_api_get_role_acl', array('id' => $roleId['id'])));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $actualAcl = ToolsAPI::jsonToArray($result->getContent());
        sort($actualAcl);

        $this->assertEquals($expectedAcl, $actualAcl);

        return $tmpExpectedAcl;
    }

    /**
     * @depends testRemoveAclsFromRole
     */
    public function testAddAclsToRole($expectedAcl)
    {
        $this->client->request('GET', $this->client->generate('oro_api_get_role_byname', array('name' => self::TEST_EDIT_ROLE)));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $roleId = ToolsAPI::jsonToArray($result->getContent());

        $this->client->request(
            'POST',
            $this->client->generate('oro_api_post_role_acl_array', array('id' => $roleId['id'])),
            array('resources' => array('oro_security','oro_address'))
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 204);

        $this->client->request('GET', $this->client->generate('oro_api_get_role_acl', array('id' => $roleId['id'])));
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
        $actualAcl = ToolsAPI::jsonToArray($result->getContent());
        sort($actualAcl);

        $this->assertEquals($expectedAcl, $actualAcl);
    }
}
