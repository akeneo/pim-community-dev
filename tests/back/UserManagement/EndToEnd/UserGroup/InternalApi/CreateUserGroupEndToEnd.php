<?php

namespace AkeneoTest\UserManagement\EndToEnd\UserGroup\InternalApi;

use AkeneoTest\Platform\EndToEnd\InternalApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class CreateUserGroupEndToEnd extends InternalApiTestCase
{
    public function testHttpHeadersInResponseWhenAUserGroupIsCreated()
    {
        $this->authenticate($this->getAdminUser());

        $data =
            <<<JSON
    {
        "name":"new_user_group"
    }
JSON;

        $this->client->request(
            'POST',
            'rest/user_group/',
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ],
            $data);

        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame('{"name":"new_user_group"}', $response->getContent());
    }

    public function testResponseWhenContentIsEmpty()
    {
        $this->authenticate($this->getAdminUser());

        $this->client->request(
            'POST',
            'rest/user_group/',
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
            'rest/user_group/',
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
        "name":"IT support"
    }
JSON;

        $expectedContent =
            <<<JSON
{
    "values": [
        {
            "path": "name",
            "message": "This value is already used.",
            "global": false
        }
    ]
}
JSON;
        $this->client->request(
            'POST',
            'rest/user_group/',
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
        "name":"new_ecommerce",
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
            'rest/user_group/',
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
