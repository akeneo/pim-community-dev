<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Connection;

use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionPasswordCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionSecretCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CheckAuthenticationSecurityEndToEnd extends ApiTestCase
{
    public function test_the_secret_regeneration_disables_the_access_token()
    {
        $apiConnection = $this->createConnection('magento');

        $apiClient = $this->createAuthenticatedClient(
            [],
            [],
            $apiConnection->clientId(),
            $apiConnection->secret(),
            $apiConnection->username(),
            $apiConnection->password()
        );

        // Assert API client
        $apiClient->request('GET', 'api/rest/v1/currencies');
        Assert::assertEquals(Response::HTTP_OK, $apiClient->getResponse()->getStatusCode());
        $apiClient->reload();
        Assert::assertEquals(Response::HTTP_OK, $apiClient->getResponse()->getStatusCode());

        // Assert DB content
        $arrayClientId = explode('_', $apiConnection->clientId());
        $dbalConnection = $this->get('database_connection');
        $results = $dbalConnection->fetchAll('SELECT id FROM pim_api_access_token WHERE client = '. $arrayClientId[0]);
        Assert::assertCount(1, $results);

        $this->regenerateClientSecret('magento');

        // Assert API client
        $apiClient->reload();
        $responseContent = json_decode($apiClient->getResponse()->getContent(), true);
        Assert::assertEquals(Response::HTTP_UNAUTHORIZED, $responseContent['code']);
        Assert::assertEquals('The access token provided is invalid.', $responseContent['message']);

        // Assert DB content
        $results = $dbalConnection->fetchAll('SELECT id FROM pim_api_access_token WHERE client = '. $arrayClientId[0]);
        Assert::assertCount(0, $results);
    }

    public function test_the_secret_regeneration_disables_the_secret()
    {
        $apiConnection = $this->createConnection('magento');

        $this->regenerateClientSecret('magento');

        $apiClient = static::createClient(['debug' => false]);
        $apiClient->request('POST', 'api/oauth/v1/token',
            [
                'username'   => $apiConnection->username(),
                'password'   => $apiConnection->password(),
                'grant_type' => 'password',
            ],
            [],
            [
                'PHP_AUTH_USER' => $apiConnection->clientId(),
                'PHP_AUTH_PW'   => $apiConnection->secret(),
                'CONTENT_TYPE'  => 'application/json',
            ]
        );
        Assert::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $apiClient->getResponse()->getStatusCode());

        $newConnection = $this->findAConnection('magento');
        Assert::assertNotEquals($apiConnection->secret(), $newConnection->secret());
    }

    public function test_the_secret_regeneration_disables_the_refresh_token()
    {
        $apiConnection = $this->createConnection('magento');
        $serverParams = [
            'PHP_AUTH_USER' => $apiConnection->clientId(),
            'PHP_AUTH_PW'   => $apiConnection->secret(),
            'CONTENT_TYPE'  => 'application/json',
        ];

        $apiClient = static::createClient(['debug' => false]);
        $apiClient->request('POST', 'api/oauth/v1/token',
            [
                'username'   => $apiConnection->username(),
                'password'   => $apiConnection->password(),
                'grant_type' => 'password',
            ],
            [],
            $serverParams
        );

        // Assert API client
        $responseContent = $apiClient->getResponse()->getContent();
        $decodedResponse = json_decode($responseContent, true);
        $authParams = ['grant_type' => 'refresh_token', 'refresh_token' => $decodedResponse['refresh_token']];

        $apiClient = static::createClient(['debug' => false]);
        $apiClient->request('POST', 'api/oauth/v1/token', $authParams, [], $serverParams);
        Assert::assertEquals(Response::HTTP_OK, $apiClient->getResponse()->getStatusCode());

        // Assert DB content
        $arrayClientId = explode('_', $apiConnection->clientId());
        $dbalConnection = $this->get('database_connection');
        $results = $dbalConnection->fetchAll('SELECT id FROM pim_api_refresh_token WHERE client = '. $arrayClientId[0]);
        Assert::assertCount(1, $results);

        $this->regenerateClientSecret('magento');

        // Assert API client
        $apiClient = static::createClient(['debug' => false]);
        $apiClient->request('POST', 'api/oauth/v1/token', $authParams, [], $serverParams);
        Assert::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $apiClient->getResponse()->getStatusCode());

        // Assert DB content
        $results = $dbalConnection->fetchAll('SELECT id FROM pim_api_refresh_token WHERE client = '. $arrayClientId[0]);
        Assert::assertCount(0, $results);
    }

    public function test_the_password_regeneration_disables_the_access_token()
    {
        $apiConnection = $this->createConnection('magento');

        $apiClient = $this->createAuthenticatedClient(
            [],
            [],
            $apiConnection->clientId(),
            $apiConnection->secret(),
            $apiConnection->username(),
            $apiConnection->password()
        );

        // Assert API client
        $apiClient->request('GET', 'api/rest/v1/currencies');
        Assert::assertEquals(Response::HTTP_OK, $apiClient->getResponse()->getStatusCode());
        $apiClient->reload();
        Assert::assertEquals(Response::HTTP_OK, $apiClient->getResponse()->getStatusCode());

        // Assert DB content
        $arrayClientId = explode('_', $apiConnection->clientId());
        $dbalConnection = $this->get('database_connection');
        $results = $dbalConnection->fetchAll('SELECT id FROM pim_api_access_token WHERE client = '. $arrayClientId[0]);
        Assert::assertCount(1, $results);

        $this->regenerateUserPassword('magento');

        // Assert API client
        $apiClient->reload();
        $responseContent = json_decode($apiClient->getResponse()->getContent(), true);
        Assert::assertEquals(Response::HTTP_UNAUTHORIZED, $responseContent['code']);
        Assert::assertEquals('The access token provided is invalid.', $responseContent['message']);

        // Assert DB content
        $results = $dbalConnection->fetchAll('SELECT id FROM pim_api_access_token WHERE client = '. $arrayClientId[0]);
        Assert::assertCount(0, $results);
    }

    public function test_the_password_regeneration_disables_the_password()
    {
        $apiConnection = $this->createConnection('magento');

        $this->regenerateUserPassword('magento');

        $apiClient = static::createClient(['debug' => false]);
        $apiClient->request('POST', 'api/oauth/v1/token',
            [
                'username'   => $apiConnection->username(),
                'password'   => $apiConnection->password(),
                'grant_type' => 'password',
            ],
            [],
            [
                'PHP_AUTH_USER' => $apiConnection->clientId(),
                'PHP_AUTH_PW'   => $apiConnection->secret(),
                'CONTENT_TYPE'  => 'application/json',
            ]
        );
        Assert::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $apiClient->getResponse()->getStatusCode());
    }

    public function test_the_password_regeneration_disables_the_refresh_token()
    {
        $apiConnection = $this->createConnection('magento');
        $serverParams = [
            'PHP_AUTH_USER' => $apiConnection->clientId(),
            'PHP_AUTH_PW'   => $apiConnection->secret(),
            'CONTENT_TYPE'  => 'application/json',
        ];

        $apiClient = static::createClient(['debug' => false]);
        $apiClient->request('POST', 'api/oauth/v1/token',
            [
                'username'   => $apiConnection->username(),
                'password'   => $apiConnection->password(),
                'grant_type' => 'password',
            ],
            [],
            $serverParams
        );

        // Assert API client
        $responseContent = $apiClient->getResponse()->getContent();
        $decodedResponse = json_decode($responseContent, true);
        $authParams = ['grant_type' => 'refresh_token', 'refresh_token' => $decodedResponse['refresh_token']];

        $apiClient = static::createClient(['debug' => false]);
        $apiClient->request('POST', 'api/oauth/v1/token', $authParams, [], $serverParams);
        Assert::assertEquals(Response::HTTP_OK, $apiClient->getResponse()->getStatusCode());

        // Assert DB content
        $arrayClientId = explode('_', $apiConnection->clientId());
        $dbalConnection = $this->get('database_connection');
        $results = $dbalConnection->fetchAll('SELECT id FROM pim_api_refresh_token WHERE client = '. $arrayClientId[0]);
        Assert::assertCount(1, $results);

        $this->regenerateUserPassword('magento');

        // Assert API client
        $apiClient = static::createClient(['debug' => false]);
        $apiClient->request('POST', 'api/oauth/v1/token', $authParams, [], $serverParams);
        Assert::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $apiClient->getResponse()->getStatusCode());

        // Assert DB content
        $results = $dbalConnection->fetchAll('SELECT id FROM pim_api_refresh_token WHERE client = '. $arrayClientId[0]);
        Assert::assertCount(0, $results);
    }

    private function regenerateClientSecret(string $connectionCode): void
    {
        $command = new RegenerateConnectionSecretCommand($connectionCode);
        $this
            ->get('akeneo_connectivity.connection.application.handler.regenerate_connection_secret')
            ->handle($command);
    }

    private function regenerateUserPassword(string $connectionCode): void
    {
        $command = new RegenerateConnectionPasswordCommand($connectionCode);
        $this
            ->get('akeneo_connectivity.connection.application.handler.regenerate_connection_password')
            ->handle($command);
    }

    private function findAConnection(string $connectionCode): ConnectionWithCredentials
    {
        $query = new FindAConnectionQuery($connectionCode);

        return $this->get('akeneo_connectivity.connection.application.handler.find_a_connection')->handle($query);
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
