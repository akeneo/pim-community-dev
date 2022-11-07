<?php

namespace Akeneo\Tool\Bundle\ApiBundle\tests\integration\Controller\Token;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetAccessTokenIntegration extends ApiTestCase
{
    public function testGetAccessTokenWithJsonContentType()
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        list($clientId, $secret) = $this->createOAuthClient();

        $client->request('POST', 'api/oauth/v1/token',
            [
               'username'   => static::USERNAME,
               'password'   => static::PASSWORD,
               'grant_type' => 'password',
            ],
            [],
            [
                'PHP_AUTH_USER' => $clientId,
                'PHP_AUTH_PW'   => $secret,
                'CONTENT_TYPE'  => 'application/json',
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

    public function testGetAccessTokenWithFormUrlEncodedContentType()
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        list($clientId, $secret) = $this->createOAuthClient();

        $client->request('POST', 'api/oauth/v1/token',
            [
                'username'   => static::USERNAME,
                'password'   => static::PASSWORD,
                'grant_type' => 'password',
            ],
            [],
            [
                'PHP_AUTH_USER' => $clientId,
                'PHP_AUTH_PW'   => $secret,
                'CONTENT_TYPE'  => 'application/x-www-form-urlencoded',
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

    public function testGetAccessTokenWithBadContentType()
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        list($clientId, $secret) = $this->createOAuthClient();

        $client->request('POST', 'api/oauth/v1/token',
            [
                'username'   => static::USERNAME,
                'password'   => static::PASSWORD,
                'grant_type' => 'password',
            ],
            [],
            [
                'PHP_AUTH_USER' => $clientId,
                'PHP_AUTH_PW'   => $secret,
                'CONTENT_TYPE'  => 'application/xml',
            ]
        );

        $expectedContent = <<<JSON
    {
        "code": 415,
        "message": "\"application\/xml\" in \"Content-Type\" header is not valid. Only \"application\/json\" or \"application\/x-www-form-urlencoded\" are allowed."
    }
JSON;

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
        $this->assertJsonStringEqualsJsonString($expectedContent, $response->getContent());
    }

    public function testMissingGrantType()
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        list($clientId, $secret) = $this->createOAuthClient();

        $client->request('POST', 'api/oauth/v1/token',
            [
                'username'   => static::USERNAME,
                'password'   => static::PASSWORD,
            ],
            [],
            [
                'PHP_AUTH_USER' => $clientId,
                'PHP_AUTH_PW'   => $secret,
                'CONTENT_TYPE'  => 'application/json',
            ]
        );

        $response = $client->getResponse();
        $responseBody = json_decode($response->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSame('Parameter "grant_type", "username" or "password" is missing, empty or invalid', $responseBody['message']);
        $this->assertArrayNotHasKey('access_token', $responseBody);
        $this->assertArrayNotHasKey('refresh_token', $responseBody);
    }

    public function testInvalidGrantType()
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        list($clientId, $secret) = $this->createOAuthClient();

        $client->request('POST', 'api/oauth/v1/token',
            [
                'username'   => static::USERNAME,
                'password'   => static::PASSWORD,
                'grant_type' => 'passwordd',
            ],
            [],
            [
                'PHP_AUTH_USER' => $clientId,
                'PHP_AUTH_PW'   => $secret,
                'CONTENT_TYPE'  => 'application/json',
            ]
        );

        $response = $client->getResponse();
        $responseBody = json_decode($response->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSame('Parameter "grant_type", "username" or "password" is missing, empty or invalid', $responseBody['message']);
        $this->assertArrayNotHasKey('access_token', $responseBody);
        $this->assertArrayNotHasKey('refresh_token', $responseBody);
    }

    public function testUnauthorizedGrantType()
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        list($clientId, $secret) = $this->createOAuthClient();

        $client->request('POST', 'api/oauth/v1/token',
            [
                'username'   => static::USERNAME,
                'password'   => static::PASSWORD,
                'grant_type' => 'token',
            ],
            [],
            [
                'PHP_AUTH_USER' => $clientId,
                'PHP_AUTH_PW'   => $secret,
                'CONTENT_TYPE'  => 'application/json',
            ]
        );

        $response = $client->getResponse();
        $responseBody = json_decode($response->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSame('This grant type is not authorized for this client', $responseBody['message']);
        $this->assertArrayNotHasKey('access_token', $responseBody);
        $this->assertArrayNotHasKey('refresh_token', $responseBody);
    }

    public function testInvalidClientId()
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        list($clientId, $secret) = $this->createOAuthClient();

        $client->request('POST', 'api/oauth/v1/token',
            [
                'username'   => static::USERNAME,
                'password'   => static::PASSWORD,
                'grant_type' => 'password',
            ],
            [],
            [
                'PHP_AUTH_USER' => 'michel_id',
                'PHP_AUTH_PW'   => $secret,
                'CONTENT_TYPE'  => 'application/json',
            ]
        );

        $response = $client->getResponse();
        $responseBody = json_decode($response->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSame('Parameter "client_id" is missing or does not match any client, or secret is invalid', $responseBody['message']);
        $this->assertArrayNotHasKey('access_token', $responseBody);
        $this->assertArrayNotHasKey('refresh_token', $responseBody);
    }

    public function testInvalidSecret()
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        list($clientId, $secret) = $this->createOAuthClient();

        $client->request('POST', 'api/oauth/v1/token',
            [
                'username'   => static::USERNAME,
                'password'   => static::PASSWORD,
                'grant_type' => 'password',
            ],
            [],
            [
                'PHP_AUTH_USER' => $clientId,
                'PHP_AUTH_PW'   => 'michel_secret',
                'CONTENT_TYPE'  => 'application/json',
            ]
        );

        $response = $client->getResponse();
        $responseBody = json_decode($response->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSame('Parameter "client_id" is missing or does not match any client, or secret is invalid', $responseBody['message']);
        $this->assertArrayNotHasKey('access_token', $responseBody);
        $this->assertArrayNotHasKey('refresh_token', $responseBody);
    }

    public function testMissingUsername()
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        list($clientId, $secret) = $this->createOAuthClient();

        $client->request('POST', 'api/oauth/v1/token',
            [
                'password'   => static::PASSWORD,
                'grant_type' => 'password',
            ],
            [],
            [
                'PHP_AUTH_USER' => $clientId,
                'PHP_AUTH_PW'   => $secret,
                'CONTENT_TYPE'  => 'application/json',
            ]
        );

        $response = $client->getResponse();
        $responseBody = json_decode($response->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSame('Parameter "grant_type", "username" or "password" is missing, empty or invalid', $responseBody['message']);
        $this->assertArrayNotHasKey('access_token', $responseBody);
        $this->assertArrayNotHasKey('refresh_token', $responseBody);
    }

    public function testMissingPassword()
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        list($clientId, $secret) = $this->createOAuthClient();

        $client->request('POST', 'api/oauth/v1/token',
            [
                'username'   => static::USERNAME,
                'grant_type' => 'password',
            ],
            [],
            [
                'PHP_AUTH_USER' => $clientId,
                'PHP_AUTH_PW'   => $secret,
                'CONTENT_TYPE'  => 'application/json',
            ]
        );

        $response = $client->getResponse();
        $responseBody = json_decode($response->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSame('Parameter "grant_type", "username" or "password" is missing, empty or invalid', $responseBody['message']);
        $this->assertArrayNotHasKey('access_token', $responseBody);
        $this->assertArrayNotHasKey('refresh_token', $responseBody);
    }

    public function testUserNotFound()
    {
        static::ensureKernelShutdown();
        $client = static::createClient();
        list($clientId, $secret) = $this->createOAuthClient();

        $client->request('POST', 'api/oauth/v1/token',
            [
                'username'   => 'michel',
                'password'   => 'michelpwd',
                'grant_type' => 'password',
            ],
            [],
            [
                'PHP_AUTH_USER' => $clientId,
                'PHP_AUTH_PW'   => $secret,
                'CONTENT_TYPE'  => 'application/json',
            ]
        );

        $response = $client->getResponse();
        $responseBody = json_decode($response->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertSame('No user found for the given username and password', $responseBody['message']);
        $this->assertArrayNotHasKey('access_token', $responseBody);
        $this->assertArrayNotHasKey('refresh_token', $responseBody);
    }

    public function testInvalidToken()
    {
        static::ensureKernelShutdown();
        $client = ApiTestCase::createClient();

        $client->request('GET', 'api/rest/v1/products/foo', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer invalidToken',
            'CONTENT_TYPE' => 'application/json',
        ]);

        $response = $client->getResponse();
        $responseBody = json_decode($response->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        $this->assertSame('The access token provided is invalid.', $responseBody['message']);
    }

    public function testGetAccessTokenWithTooLargeRequestContent()
    {
        static::ensureKernelShutdown();
        $client = static::createClient();

        $client->request(
            'POST',
            'api/oauth/v1/token',
            [],
            [],
            [],
            json_encode([
                'large_content' => str_repeat('a', 300)
            ])
        );

        $expectedContent = <<<JSON
    {
        "code": 413,
        "message": "Request content exceeded the maximum allowed size of 300 bytes"
    }
JSON;

        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
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
