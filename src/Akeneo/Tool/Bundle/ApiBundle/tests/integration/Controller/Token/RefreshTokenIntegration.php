<?php

namespace Akeneo\Tool\Bundle\ApiBundle\tests\integration\Controller\Token;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class RefreshTokenIntegration extends ApiTestCase
{
    public function testRefreshToken()
    {
        list($clientId, $secret) = $this->createOAuthClient();
        list($accessToken, $refreshToken) = $this->authenticate($clientId, $secret, self::USERNAME, self::PASSWORD);

        $client = $this->createAuthenticatedClient(
            [],
            [],
            $clientId,
            $secret,
            self::USERNAME,
            self::PASSWORD,
            $accessToken,
            $refreshToken
        );

        $client->request('POST', 'api/oauth/v1/token',
            [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $refreshToken,
            ],
            [],
            [
                'PHP_AUTH_USER' => $clientId,
                'PHP_AUTH_PW'   => $secret,
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

    public function testRefreshTokenWithFormUrlEncodedContentType()
    {
        list($clientId, $secret) = $this->createOAuthClient();
        list($accessToken, $refreshToken) = $this->authenticate($clientId, $secret, self::USERNAME, self::PASSWORD);

        $client = $this->createAuthenticatedClient(
            [],
            [],
            $clientId,
            $secret,
            self::USERNAME,
            self::PASSWORD,
            $accessToken,
            $refreshToken
        );

        $client->request('POST', 'api/oauth/v1/token',
            [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $refreshToken,
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

    public function testMissingRefreshToken()
    {
        list($clientId, $secret) = $this->createOAuthClient();
        $client = $this->createAuthenticatedClient([], [], $clientId, $secret);

        $client->request('POST', 'api/oauth/v1/token',
            [
                'grant_type' => 'refresh_token'
            ],
            [],
            [
                'PHP_AUTH_USER' => $clientId,
                'PHP_AUTH_PW'   => $secret,
            ]
        );

        $response = $client->getResponse();
        $responseBody = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame('Parameter "grant_type" or "refresh_token" is missing or empty', $responseBody['message']);
        $this->assertArrayNotHasKey('access_token', $responseBody);
        $this->assertArrayNotHasKey('refresh_token', $responseBody);
    }

    public function testInvalidRefreshToken()
    {
        list($clientId, $secret) = $this->createOAuthClient();
        $client = $this->createAuthenticatedClient([], [], $clientId, $secret);

        $client->request('POST', 'api/oauth/v1/token',
            [
                'grant_type'    => 'refresh_token',
                'refresh_token' => 'ihopeitwontbeafalsepositivesomeday',
            ],
            [],
            [
                'PHP_AUTH_USER' => $clientId,
                'PHP_AUTH_PW'   => $secret,
            ]
        );

        $response = $client->getResponse();
        $responseBody = json_decode($response->getContent(), true);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertSame('Refresh token is invalid or has expired', $responseBody['message']);
        $this->assertArrayNotHasKey('access_token', $responseBody);
        $this->assertArrayNotHasKey('refresh_token', $responseBody);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
