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
    /**
     * @var Client
     */
    protected $client = null;

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
        $result = $this->client->soapClient->getAclIds();
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
        foreach ($acls as $acl) {
            $result = $this->client->soapClient->getAcl($acl);
        }
    }

    /**
     * @param $acls
     * @return array
     * @depends testGetAcls
     */
    public function testGetRoleAcl($acls)
    {
        $roleId =  $this->client->soapClient->getRoleByName(self::TEST_ROLE);
        $roleId = ToolsAPI::classToArray($roleId);
        $result =  $this->client->soapClient->getRoleAcl($roleId['id']);
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
        $userId = $this->client->soapClient->getUserBy(array('item' => array('key' =>'username', 'value' =>'admin')));
        $userId = ToolsAPI::classToArray($userId);
        $result =  $this->client->soapClient->getUserAcl($userId['id']);
        $result = ToolsAPI::classToArray($result);
        $result = $result['item'];
        sort($result);
        $this->assertEquals($acls, $result);
    }

    public function testRemoveAclFromRole()
    {
        $this->markTestSkipped('BAP-977');
    }

    /**
     * @depends testRemoveAclFromRole
     */
    public function testAddAclToRole()
    {
        $this->markTestSkipped('BAP-977');
    }

    public function testRemoveAclsFromRole()
    {
        $this->markTestSkipped('BAP-977');
    }

    /**
     * @depends testRemoveAclsFromRole
     */
    public function testAddAclsToRole()
    {
        $this->markTestSkipped('BAP-977');
    }
}
