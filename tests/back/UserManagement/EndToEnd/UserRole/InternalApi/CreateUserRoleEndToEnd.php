<?php

namespace AkeneoTest\UserManagement\EndToEnd\UserGroup\InternalApi;

use AkeneoTest\Platform\EndToEnd\InternalApiTestCase;
use Doctrine\DBAL\Connection as DbalConnection;
use Symfony\Component\HttpFoundation\Response;

class CreateUserRoleEndToEnd extends InternalApiTestCase
{

    private DbalConnection $dbalConnection;

    public function setUp(): void
    {
        parent::setUp();

        $this->dbalConnection = $this->get('database_connection');
        $this->authenticate($this->getAdminUser());
    }

    public function testHttpHeadersInResponseWhenAUserGroupIsCreated()
    {
        $this->authenticate($this->getAdminUser());

        $data =
            <<<JSON
{"role":"ROLE_TEST","label":"TEST","permissions":"action:oro_config_system"}
JSON;

        $this->client->request(
            'POST',
            'rest/user_role/',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            $data);

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame($data, $response->getContent());
    }

    public function testResponseWhenContentIsEmpty()
    {
        $this->authenticate($this->getAdminUser());

        $this->client->request(
            'POST',
            'rest/user_role/',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            '');

        $expectedContent =
            <<<JSON
{
	"message": "Invalid json message received"
}
JSON;

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenContentIsNotValid()
    {
        $this->authenticate($this->getAdminUser());

        $this->client->request(
            'POST',
            'rest/user_role/',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            '{'
        );

        $expectedContent =
            <<<JSON
{
	"message": "Invalid json message received"
}
JSON;

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenUserGroupAlreadyExists()
    {
        $this->authenticate($this->getAdminUser());

        $data =
            <<<JSON
    {
        "role":"ROLE_ADMINISTRATOR",
        "label":"ROLE admin"
    }
JSON;

        $expectedContent =
            <<<JSON
{
    "values": [
        {
            "global": false,
            "message": "This value is already used.",
            "path": "role.role"
        }
    ]
}
JSON;
        $this->client->request(
            'POST',
            'rest/user_role/',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            $data);

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testResponseWhenAPropertyIsNotExpected()
    {
        $this->authenticate($this->getAdminUser());

        $data =
            <<<JSON
    {
        "role":"ROLE_NEW_TEST",
        "extra_property": ""
    }
JSON;

        $expectedContent =
            <<<JSON
    {
        "message":"Property \u0022extra_property\u0022 does not exist."
    }
JSON;
        $this->client->request(
            'POST',
            'rest/user_role/',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            $data);

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
