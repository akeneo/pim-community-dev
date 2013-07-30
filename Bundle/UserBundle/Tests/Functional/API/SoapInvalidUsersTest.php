<?php

namespace Oro\Bundle\UserBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 */
class SoapInvalidUsersTest extends WebTestCase
{

    const USER_NAME = 'user_wo_permissions';
    const USER_PASSWORD = 'no_key';

    /** @var Client */
    protected $client;

    public function tearDown()
    {
        unset($this->client);
    }

    public function testInvalidKey()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader(ToolsAPI::USER_NAME, self::USER_PASSWORD));
        try {
            $this->client->soap(
                "http://localhost/api/soap",
                array(
                    'location' => 'http://localhost/api/soap',
                    'soap_version' => SOAP_1_2
                )
            );
        } catch (\Exception $e) {
            $this->assertEquals('Unauthorized', $e->getMessage());
        }
    }

    public function testInvalidUser()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateWsseHeader(self::USER_NAME, ToolsAPI::USER_PASSWORD));
        try {
            $this->client->soap(
                "http://localhost/api/soap",
                array(
                    'location' => 'http://localhost/api/soap',
                    'soap_version' => SOAP_1_2
                )
            );
        } catch (\Exception $e) {
            $this->assertEquals('Unauthorized', $e->getMessage());
        }
    }
}
