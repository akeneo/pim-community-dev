<?php

namespace Oro\Bundle\DataAuditBundle\Tests;

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
        if (!isset($this->client)) {
            $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader());
            $this->client->soap(
                "http://localhost/api/soap",
                array(
                    'location' => 'http://localhost/api/soap',
                    'soap_version' => SOAP_1_2
                )
            );

        } else {
            $this->client->restart();
        }
    }

    /**
     * @return array
     */
    public function testPreconditions()
    {
        //clear Audits
        $result = $this->client->soapClient->getAudits();
        $result = ToolsAPI::classToArray($result);
        if (!empty($result)) {
            if (!is_array(reset($result['item']))) {
                $result[] = $result['item'];
                unset($result['item']);
            } else {
                $result = $result['item'];
            }
            foreach ($result as $audit) {
                $this->client->soapClient->deleteAudit($audit['id']);
            }
        }

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
        $result = $this->client->soapClient->getAudits();
        $result = ToolsAPI::classToArray($result);

        if (!is_array(reset($result['item']))) {
            $result[] = $result['item'];
            unset($result['item']);
        } else {
            $result = $result['item'];
        }

        $resultActual = reset($result);
        //Bug BAP-1116
        //$this->assertEquals($resultExpected['action'], 'create');
        //$this->assertEquals($resultExpected['objectClass'], 'Oro\Bundle\UserBundle\Entity\User');
        $this->assertEquals($response['username'], $resultActual['objectName']);
        $this->assertEquals('admin', $resultActual['user']['username']);

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
            $result = $this->client->soapClient->getAudit($audit['id']);
            $result = ToolsAPI::classToArray($result);
            unset($result['loggedAt']);
            unset($audit['loggedAt']);
            $this->assertEquals($audit, $result);
        }
    }

    /**
     * @param array $response
     * @depends testGetAudits
     */
    public function testDeleteAudit($response)
    {
        foreach ($response as $audit) {
            $this->client->soapClient->deleteAudit($audit['id']);
        }
        $result = $this->client->soapClient->getAudits();
        $result = ToolsAPI::classToArray($result);
        $this->assertEmpty($result);
    }
}
