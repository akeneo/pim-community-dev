<?php

namespace Oro\Bundle\DataAuditBundle\Tests;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * db_isolation
 */
class SoapDataAuditApiTest extends WebTestCase
{

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
    public function testPreconditions()
    {
        //create users
        $request = array(
            "username" => 'user_' . mt_rand(),
            "email" => 'test_'  . mt_rand() . '@test.com',
            "enabled" => '1',
            "plainPassword" => '1231231q',
            "firstName" => "firstName",
            "lastName" => "lastName",
            "rolesCollection" => array("1")
        );
        $result = $this->client->soapClient->createUser($request);
        $this->assertTrue($result, $this->client->soapClient->__getLastResponse());
        return $request;
    }

    /**
     * @param $response
     * @return array
     * @depends testPreconditions
     */
    public function testGetAudits($response)
    {
        $this->markTestSkipped('BAP-949');
        $result = $this->client->soapClient->getAudits();
        return $result;
    }

    /**
     * @param array $response
     * @return array
     * @depends testGetAudits
     */
    public function testGetAudit($response)
    {
        $result = $this->client->soapClient->getAudit($response[0]['id']);
        return $result;
    }

    /**
     * @param array $response
     * @depends testGetAudit
     */
    public function testDeleteAudit($response)
    {
        $this->client->soapClient->deleteAudit($response[0]['id']);
    }
}
