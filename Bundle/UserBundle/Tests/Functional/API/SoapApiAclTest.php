<?php

namespace Oro\Bundle\UserBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class SoapApiAclTest extends WebTestCase
{
    const TEST_ROLE = 'ROLE_SUPER_ADMIN';
    const TEST_EDIT_ROLE = 'ROLE_USER';

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
     * @return array
     */
    public function testGetAcls()
    {
        $result = $this->client->getSoap()->getAclIds();
        $result = ToolsAPI::classToArray($result);
        $result = $result['item'];
        sort($result);
        return $result;
    }

    /**
     * @param array $acls
     * @depends testGetAcls
     */
    public function testGetAcl($acls)
    {
        $i = 0;
        foreach ($acls as $acl) {
            $result = $this->client->getSoap()->getAcl($acl);
            $result = ToolsAPI::classToArray($result);
            $this->assertEquals($acl, $result['id']);
            $i++;
            if ($i % 10 == 0) {
                break;
            }
        }
    }

    /**
     * @param $acls
     * @return array
     * @depends testGetAcls
     */
    public function testGetRoleAcl($acls)
    {
        $roleId =  $this->client->getSoap()->getRoleByName(self::TEST_ROLE);
        $roleId = ToolsAPI::classToArray($roleId);
        $result =  $this->client->getSoap()->getRoleAcl($roleId['id']);
        $result = ToolsAPI::classToArray($result);
        $result = $result['item'];
        sort($result);
        $this->assertEquals($acls, $result);
        return $result;
    }

    /**
     * @param $acls
     * @depends testGetRoleAcl
     */
    public function testGetUserAcl($acls)
    {
        $userId = $this->client->getSoap()->getUserBy(array('item' => array('key' =>'username', 'value' =>'admin')));
        $userId = ToolsAPI::classToArray($userId);
        $result =  $this->client->getSoap()->getUserAcl($userId['id']);
        $result = ToolsAPI::classToArray($result);
        $result = $result['item'];
        sort($result);
        $this->assertEquals($acls, $result);
    }

    public function testRemoveAclFromRole()
    {
        $roleId =  $this->client->getSoap()->getRoleByName(self::TEST_EDIT_ROLE);
        $roleId = ToolsAPI::classToArray($roleId);

        $result =  $this->client->getSoap()->getRoleAcl($roleId['id']);
        $result = ToolsAPI::classToArray($result);
        $expectedAcl = $result['item'];

        $tmpExpectedAcl = $expectedAcl;

        foreach ($expectedAcl as $key => $val) {
            // root resource will be deleted after any resource delete
            if (preg_match('/oro_address*/', $val) || $val == 'root') {
                unset($expectedAcl[ $key ]);
            }
        }
        sort($expectedAcl);

        $this->client->getSoap()->removeAclFromRole($roleId['id'], 'oro_address');
        $result =  $this->client->getSoap()->getRoleAcl($roleId['id']);
        $result = ToolsAPI::classToArray($result);
        $actualAcl = $result['item'];
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
        $roleId =  $this->client->getSoap()->getRoleByName(self::TEST_EDIT_ROLE);
        $roleId = ToolsAPI::classToArray($roleId);

        $this->client->getSoap()->addAclToRole($roleId['id'], 'oro_address');
        $this->client->getSoap()->addAclToRole($roleId['id'], 'root');

        $result =  $this->client->getSoap()->getRoleAcl($roleId['id']);
        $result = ToolsAPI::classToArray($result);
        $actualAcl = $result['item'];
        sort($actualAcl);
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
        $roleId =  $this->client->getSoap()->getRoleByName(self::TEST_EDIT_ROLE);
        $roleId = ToolsAPI::classToArray($roleId);

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

        $this->client->getSoap()->removeAclsFromRole($roleId['id'], array('oro_security','oro_address'));

        $result =  $this->client->getSoap()->getRoleAcl($roleId['id']);
        $result = ToolsAPI::classToArray($result);
        $actualAcl = $result['item'];
        sort($actualAcl);

        $this->assertEquals($expectedAcl, $actualAcl);

        return $tmpExpectedAcl;
    }

    /**
     * @depends testRemoveAclsFromRole
     */
    public function testAddAclsToRole($expectedAcl)
    {
        $roleId =  $this->client->getSoap()->getRoleByName(self::TEST_EDIT_ROLE);
        $roleId = ToolsAPI::classToArray($roleId);

        $this->client->getSoap()->addAclsToRole($roleId['id'], array('oro_security','oro_address'));

        $result =  $this->client->getSoap()->getRoleAcl($roleId['id']);
        $result = ToolsAPI::classToArray($result);
        $actualAcl = $result['item'];
        sort($actualAcl);

        $this->assertEquals($expectedAcl, $actualAcl);
    }
}
