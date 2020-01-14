<?php

namespace Akeneo\Tool\Bundle\ApiBundle\tests\integration\Client;

use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionSecretCommand;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;

class RevokeTokenIntegration extends ApiTestCase
{
    public function testCascadeDeleteRefreshToken()
    {
        $client = static::createClient();
        list($clientId, $secret) = $this->createOAuthClient('Revoke_Token_Test');

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

        $arrayClientId = explode('_', $clientId);

        $connection = $this->get('doctrine.orm.default_entity_manager')->getConnection();
        $stmt = $connection->prepare('SELECT client, user, token from pim_api_refresh_token where client = :client');
        $stmt->bindParam('client', $arrayClientId[0]);
        $stmt->execute();
        $result = $stmt->fetch();

        $this->assertSame(1, $this->count($result));

        $this->revokeConnection('Revoke_Token_Test');

        $stmt->bindParam('client', $arrayClientId[0]);
        $stmt->execute();
        $result = $stmt->fetch();

        $this->assertSame(false, $result);
    }

    public function testCascadeDeleteAccessToken()
    {
        $client = static::createClient();
        list($clientId, $secret) = $this->createOAuthClient('Revoke_Token_Test');

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

        $arrayClientId = explode('_', $clientId);

        $connection = $this->get('doctrine.orm.default_entity_manager')->getConnection();
        $stmt = $connection->prepare('SELECT client, user, token from pim_api_access_token where client = :client');
        $stmt->bindParam('client', $arrayClientId[0]);
        $stmt->execute();
        $result = $stmt->fetch();

        $this->assertSame(1, $this->count($result));

        $this->revokeConnection('Revoke_Token_Test');

        $stmt->bindParam('client', $arrayClientId[0]);
        $stmt->execute();
        $result = $stmt->fetch();

        $this->assertSame(false, $result);
    }

    public function testResponseWhenUseARevokedToken()
    {
        list($clientId, $secret) = $this->createOAuthClient('Revoke_Token_Test');
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

        $client->request('GET', '/api/rest/v1/currencies/eur');
        $response = $client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $this->revokeConnection('Revoke_Token_Test');

        $client->request('GET', '/api/rest/v1/currencies/eur');

        $expected =
<<<JSON
{
    "code": 401,
    "message": "The access token provided is invalid."
}
JSON;

        $response = $client->getResponse();
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    public function testRefreshTokenResponseWithRevokedToken()
    {
        list($clientId, $secret) = $this->createOAuthClient('Revoke_Token_Test');
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

        $client->request('POST', '/api/oauth/v1/token',
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
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $this->revokeConnection('Revoke_Token_Test');

        $client->request('POST', '/api/oauth/v1/token',
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

        $expected =
<<<JSON
{
    "code": 422,
    "message": "Parameter \"client_id\" is missing or does not match any client, or secret is invalid"
}
JSON;

        $response = $client->getResponse();
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * Revoke a client using command line.
     *
     * @deprecated
     * @param $clientId
     *
     * @return string
     */
    protected function revokeOAuthClient($clientId)
    {
        $consoleApp = new Application(static::$kernel);
        $consoleApp->setAutoExit(false);

        $input  = new ArrayInput([
            'command'   => 'pim:oauth-server:revoke-client',
            'client_id' => $clientId,
        ]);
        $output = new BufferedOutput();

        $consoleApp->run($input, $output);

        return $output->fetch();
    }

    private function revokeConnection(string $connectionCode): void
    {
        $command = new RegenerateConnectionSecretCommand($connectionCode);
        $this->get('akeneo_connectivity.connection.application.handler.regenerate_connection_secret')->handle($command);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
