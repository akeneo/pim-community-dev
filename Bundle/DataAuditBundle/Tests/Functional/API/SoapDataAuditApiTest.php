<?php

namespace Oro\Bundle\DataAuditBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class SoapDataAuditApiTest extends WebTestCase
{
    /** @var Client  */
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
            "namePrefix" => "Mr",
            "firstName" => "firstName",
            "middleName" => "middleName",
            "lastName" => "lastName",
            "nameSuffix" => "Sn.",
            "rolesCollection" => array("2"),
            "owner" => "1"
        );

        $id = $this->client->getSoap()->createUser($request);
        $this->assertInternalType('int', $id, $this->client->getSoap()->__getLastResponse());
        $this->assertGreaterThan(0, $id);

        return $request;
    }

    /**
     * @param $response
     * @return array
     * @depends testPreconditions
     */
    public function testGetAudits($response)
    {
        $result = $this->client->getSoap()->getAudits();
        $result = ToolsAPI::classToArray($result);

        if (!is_array(reset($result['item']))) {
            $result[] = $result['item'];
            unset($result['item']);
        } else {
            $result = $result['item'];
        }

        $resultActual = reset($result);

        $this->assertEquals($response['username'], $resultActual['objectName']);
        $this->assertEquals('admin', $resultActual['username']);

        return $result;
    }

    /**
     * @param  array $response
     * @return array
     * @depends testGetAudits
     */
    public function testGetAudit($response)
    {
        foreach ($response as $audit) {
            $result = $this->client->getSoap()->getAudit($audit['id']);
            $result = ToolsAPI::classToArray($result);
            unset($result['loggedAt']);
            unset($audit['loggedAt']);
            $this->assertEquals($audit, $result);
        }
    }
}
