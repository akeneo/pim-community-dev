<?php

namespace tests\integration\Pim\Bundle\ApiBundle\Controller\Token;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;

class GetAccessTokenIntegration extends TestCase
{
    /** @var string */
    protected $clientId;

    /** @var string */
    protected $secret;

    protected function setUp()
    {
        parent::setUp();

        $consoleApp = new Application(self::$kernel);
        $consoleApp->setAutoExit(false);

        $input = new ArrayInput(['command' => 'pim:oauth-server:create-client']);
        $output = new BufferedOutput();

        $consoleApp->run($input, $output);

        $content = $output->fetch();
        preg_match('/client_id: (.+)\nsecret: (.+)$/', $content, $matches);
        $this->clientId = $matches[1];
        $this->secret = $matches[2];
    }

    public function testGetAccessToken()
    {
        $client = static::createClient();

        $client->request('POST', 'api/oauth/v1/token',
            [
               'username'   => 'admin',
               'password'   => 'admin',
               'grant_type' => 'password',
            ],
            [],
            [
                'PHP_AUTH_USER' => $this->clientId,
                'PHP_AUTH_PW'   => $this->secret,
            ]
        );

        $response = $client->getResponse();
        $responseBody = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertArrayHasKey('access_token', $responseBody);
        $this->assertArrayHasKey('expires_in', $responseBody);
        $this->assertArrayHasKey('token_type', $responseBody);
        $this->assertArrayHasKey('scope', $responseBody);
        $this->assertArrayHasKey('refresh_token', $responseBody);
    }

    public function testMissingGrantType()
    {
        $client = static::createClient();

        $client->request('POST', 'api/oauth/v1/token',
            [
                'username'   => 'admin',
                'password'   => 'admin',
            ],
            [],
            [
                'PHP_AUTH_USER' => $this->clientId,
                'PHP_AUTH_PW'   => $this->secret,
            ]
        );

        $response = $client->getResponse();
        $responseBody = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame('Parameter "grant_type", "username" or "password" is missing, empty or invalid', $responseBody['message']);
        $this->assertArrayNotHasKey('access_token', $responseBody);
        $this->assertArrayNotHasKey('refresh_token', $responseBody);
    }

    public function testInvalidGrantType()
    {
        $client = static::createClient();

        $client->request('POST', 'api/oauth/v1/token',
            [
                'username'   => 'admin',
                'password'   => 'admin',
                'grant_type' => 'passwordd',
            ],
            [],
            [
                'PHP_AUTH_USER' => $this->clientId,
                'PHP_AUTH_PW'   => $this->secret,
            ]
        );

        $response = $client->getResponse();
        $responseBody = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame('Parameter "grant_type", "username" or "password" is missing, empty or invalid', $responseBody['message']);
        $this->assertArrayNotHasKey('access_token', $responseBody);
        $this->assertArrayNotHasKey('refresh_token', $responseBody);
    }

    public function testUnauthorizedGrantType()
    {
        $client = static::createClient();

        $client->request('POST', 'api/oauth/v1/token',
            [
                'username'   => 'admin',
                'password'   => 'admin',
                'grant_type' => 'token',
            ],
            [],
            [
                'PHP_AUTH_USER' => $this->clientId,
                'PHP_AUTH_PW'   => $this->secret,
            ]
        );

        $response = $client->getResponse();
        $responseBody = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame('This grant type is not authorized for this client', $responseBody['message']);
        $this->assertArrayNotHasKey('access_token', $responseBody);
        $this->assertArrayNotHasKey('refresh_token', $responseBody);
    }

    public function testInvalidClientId()
    {
        $client = static::createClient();

        $client->request('POST', 'api/oauth/v1/token',
            [
                'username'   => 'admin',
                'password'   => 'admin',
                'grant_type' => 'password',
            ],
            [],
            [
                'PHP_AUTH_USER' => 'michel_id',
                'PHP_AUTH_PW'   => $this->secret,
            ]
        );

        $response = $client->getResponse();
        $responseBody = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame('Parameter "client_id" is missing or does not match any client, or secret is invalid', $responseBody['message']);
        $this->assertArrayNotHasKey('access_token', $responseBody);
        $this->assertArrayNotHasKey('refresh_token', $responseBody);
    }

    public function testInvalidSecret()
    {
        $client = static::createClient();

        $client->request('POST', 'api/oauth/v1/token',
            [
                'username'   => 'admin',
                'password'   => 'admin',
                'grant_type' => 'password',
            ],
            [],
            [
                'PHP_AUTH_USER' => $this->clientId,
                'PHP_AUTH_PW'   => 'michel_secret',
            ]
        );

        $response = $client->getResponse();
        $responseBody = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame('Parameter "client_id" is missing or does not match any client, or secret is invalid', $responseBody['message']);
        $this->assertArrayNotHasKey('access_token', $responseBody);
        $this->assertArrayNotHasKey('refresh_token', $responseBody);
    }

    public function testMissingUsername()
    {
        $client = static::createClient();

        $client->request('POST', 'api/oauth/v1/token',
            [
                'password'   => 'admin',
                'grant_type' => 'password',
            ],
            [],
            [
                'PHP_AUTH_USER' => $this->clientId,
                'PHP_AUTH_PW'   => $this->secret,
            ]
        );

        $response = $client->getResponse();
        $responseBody = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame('Parameter "grant_type", "username" or "password" is missing, empty or invalid', $responseBody['message']);
        $this->assertArrayNotHasKey('access_token', $responseBody);
        $this->assertArrayNotHasKey('refresh_token', $responseBody);
    }

    public function testMissingPassword()
    {
        $client = static::createClient();

        $client->request('POST', 'api/oauth/v1/token',
            [
                'username'   => 'admin',
                'grant_type' => 'password',
            ],
            [],
            [
                'PHP_AUTH_USER' => $this->clientId,
                'PHP_AUTH_PW'   => $this->secret,
            ]
        );

        $response = $client->getResponse();
        $responseBody = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame('Parameter "grant_type", "username" or "password" is missing, empty or invalid', $responseBody['message']);
        $this->assertArrayNotHasKey('access_token', $responseBody);
        $this->assertArrayNotHasKey('refresh_token', $responseBody);
    }

    public function testUserNotFound()
    {
        $client = static::createClient();

        $client->request('POST', 'api/oauth/v1/token',
            [
                'username'   => 'michel',
                'password'   => 'michelpwd',
                'grant_type' => 'password',
            ],
            [],
            [
                'PHP_AUTH_USER' => $this->clientId,
                'PHP_AUTH_PW'   => $this->secret,
            ]
        );

        $response = $client->getResponse();
        $responseBody = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame('No user found for the given username and password', $responseBody['message']);
        $this->assertArrayNotHasKey('access_token', $responseBody);
        $this->assertArrayNotHasKey('refresh_token', $responseBody);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getTechnicalCatalogPath()],
            false
        );
    }
}
