<?php

namespace Oro\Bundle\UserBundle\Tests\Functional\API;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;

/**
 * @outputBuffering enabled
 * @db_isolation
 */
class RestInvalidUsersTest extends WebTestCase
{

    const USER_NAME = 'user_wo_permissions';
    const USER_PASSWORD = 'no_key';

    /** @var Client */
    protected $client;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testInvalidKey()
    {
        $request = array(
            "user" => array (
                "username" => 'user_' . mt_rand(),
                "email" => 'test_'  . mt_rand() . '@test.com',
                "enabled" => 'true',
                "plainPassword" => '1231231q',
                "firstName" => "firstName",
                "lastName" => "lastName",
                "rolesCollection" => array("1")
            )
        );
        $this->client->request(
            'POST',
            $this->client->generate('oro_api_post_user'),
            $request,
            array(),
            array(),
            ToolsAPI::generateWsseHeader(ToolsAPI::USER_NAME, self::USER_PASSWORD)
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 401);
    }

    public function testInvalidUser()
    {
        $request = array(
            "user" => array (
                "username" => 'user_' . mt_rand(),
                "email" => 'test_'  . mt_rand() . '@test.com',
                "enabled" => 'true',
                "plainPassword" => '1231231q',
                "firstName" => "firstName",
                "lastName" => "lastName",
                "rolesCollection" => array("1")
            )
        );
        $this->client->request(
            'POST',
            $this->client->generate('oro_api_post_user'),
            $request,
            array(),
            array(),
            ToolsAPI::generateWsseHeader(self::USER_NAME, ToolsAPI::USER_PASSWORD)
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 401);
    }
}
