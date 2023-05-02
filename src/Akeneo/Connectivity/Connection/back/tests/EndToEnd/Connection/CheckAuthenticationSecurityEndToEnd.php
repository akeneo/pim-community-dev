<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\Connection;

use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionPasswordCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionPasswordHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionSecretCommand;
use Akeneo\Connectivity\Connection\Application\Settings\Command\RegenerateConnectionSecretHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionHandler;
use Akeneo\Connectivity\Connection\Application\Settings\Query\FindAConnectionQuery;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\ConnectionWithCredentials;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
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
    public function test_the_secret_regeneration_disables_the_access_token(): void
    {
        $apiConnection = $this->createConnection('magento', 'Magento', FlowType::DATA_DESTINATION);

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
        $arrayClientId = \explode('_', $apiConnection->clientId());
        $dbalConnection = $this->get('database_connection');
        $results = $dbalConnection->fetchAllAssociative('SELECT id FROM pim_api_access_token WHERE client = '. $arrayClientId[0]);
        Assert::assertCount(1, $results);

        $this->regenerateClientSecret('magento');

        // Assert API client
        $apiClient->reload();
        $responseContent = \json_decode($apiClient->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        Assert::assertEquals(Response::HTTP_UNAUTHORIZED, $responseContent['code']);
        Assert::assertEquals('The access token provided is invalid.', $responseContent['message']);

        // Assert DB content
        $results = $dbalConnection->fetchAllAssociative('SELECT id FROM pim_api_access_token WHERE client = '. $arrayClientId[0]);
        Assert::assertCount(0, $results);
    }

    public function test_the_secret_regeneration_disables_the_secret(): void
    {
        $apiConnection = $this->createConnection('magento', 'Magento', FlowType::DATA_DESTINATION);

        $this->regenerateClientSecret('magento');

        static::ensureKernelShutdown();
        $apiClient = static::createClient(['debug' => false]);
        $apiClient->request(
            'POST',
            'api/oauth/v1/token',
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

    public function test_the_secret_regeneration_disables_the_refresh_token(): void
    {
        $apiConnection = $this->createConnection('magento', 'Magento', FlowType::DATA_DESTINATION);
        $serverParams = [
            'PHP_AUTH_USER' => $apiConnection->clientId(),
            'PHP_AUTH_PW'   => $apiConnection->secret(),
            'CONTENT_TYPE'  => 'application/json',
        ];

        static::ensureKernelShutdown();
        $apiClient = static::createClient(['debug' => false]);
        $apiClient->request(
            'POST',
            'api/oauth/v1/token',
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
        $decodedResponse = \json_decode($responseContent, true, 512, JSON_THROW_ON_ERROR);
        $authParams = ['grant_type' => 'refresh_token', 'refresh_token' => $decodedResponse['refresh_token']];

        static::ensureKernelShutdown();
        $apiClient = static::createClient(['debug' => false]);
        $apiClient->request('POST', 'api/oauth/v1/token', $authParams, [], $serverParams);
        Assert::assertEquals(Response::HTTP_OK, $apiClient->getResponse()->getStatusCode());

        // Assert DB content
        $arrayClientId = \explode('_', $apiConnection->clientId());
        $dbalConnection = $this->get('database_connection');
        $results = $dbalConnection->fetchAllAssociative('SELECT id FROM pim_api_refresh_token WHERE client = '. $arrayClientId[0]);
        Assert::assertCount(1, $results);

        $this->regenerateClientSecret('magento');

        // Assert API client
        static::ensureKernelShutdown();
        $apiClient = static::createClient(['debug' => false]);
        $apiClient->request('POST', 'api/oauth/v1/token', $authParams, [], $serverParams);
        Assert::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $apiClient->getResponse()->getStatusCode());

        // Assert DB content
        $results = $dbalConnection->fetchAllAssociative('SELECT id FROM pim_api_refresh_token WHERE client = '. $arrayClientId[0]);
        Assert::assertCount(0, $results);
    }

    public function test_the_password_regeneration_disables_the_access_token(): void
    {
        $apiConnection = $this->createConnection('magento', 'Magento', FlowType::DATA_DESTINATION);

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
        $arrayClientId = \explode('_', $apiConnection->clientId());
        $dbalConnection = $this->get('database_connection');
        $results = $dbalConnection->fetchAllAssociative('SELECT id FROM pim_api_access_token WHERE client = '. $arrayClientId[0]);
        Assert::assertCount(1, $results);

        $this->regenerateUserPassword('magento');

        // Assert API client
        $apiClient->reload();
        $responseContent = \json_decode($apiClient->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        Assert::assertEquals(Response::HTTP_UNAUTHORIZED, $responseContent['code']);
        Assert::assertEquals('The access token provided is invalid.', $responseContent['message']);

        // Assert DB content
        $results = $dbalConnection->fetchAllAssociative('SELECT id FROM pim_api_access_token WHERE client = '. $arrayClientId[0]);
        Assert::assertCount(0, $results);
    }

    public function test_the_password_regeneration_disables_the_password(): void
    {
        $apiConnection = $this->createConnection('magento', 'Magento', FlowType::DATA_DESTINATION);

        $this->regenerateUserPassword('magento');

        static::ensureKernelShutdown();
        $apiClient = static::createClient(['debug' => false]);
        $apiClient->request(
            'POST',
            'api/oauth/v1/token',
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

    public function test_the_password_regeneration_disables_the_refresh_token(): void
    {
        $apiConnection = $this->createConnection('magento', 'Magento', FlowType::DATA_DESTINATION);
        $serverParams = [
            'PHP_AUTH_USER' => $apiConnection->clientId(),
            'PHP_AUTH_PW'   => $apiConnection->secret(),
            'CONTENT_TYPE'  => 'application/json',
        ];

        static::ensureKernelShutdown();
        $apiClient = static::createClient(['debug' => false]);
        $apiClient->request(
            'POST',
            'api/oauth/v1/token',
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
        $decodedResponse = \json_decode($responseContent, true, 512, JSON_THROW_ON_ERROR);
        $authParams = ['grant_type' => 'refresh_token', 'refresh_token' => $decodedResponse['refresh_token']];

        static::ensureKernelShutdown();
        $apiClient = static::createClient(['debug' => false]);
        $apiClient->request('POST', 'api/oauth/v1/token', $authParams, [], $serverParams);
        Assert::assertEquals(Response::HTTP_OK, $apiClient->getResponse()->getStatusCode());

        // Assert DB content
        $arrayClientId = \explode('_', $apiConnection->clientId());
        $dbalConnection = $this->get('database_connection');
        $results = $dbalConnection->fetchAllAssociative('SELECT id FROM pim_api_refresh_token WHERE client = '. $arrayClientId[0]);
        Assert::assertCount(1, $results);

        $this->regenerateUserPassword('magento');

        // Assert API client
        static::ensureKernelShutdown();
        $apiClient = static::createClient(['debug' => false]);
        $apiClient->request('POST', 'api/oauth/v1/token', $authParams, [], $serverParams);
        Assert::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $apiClient->getResponse()->getStatusCode());

        // Assert DB content
        $results = $dbalConnection->fetchAllAssociative('SELECT id FROM pim_api_refresh_token WHERE client = '. $arrayClientId[0]);
        Assert::assertCount(0, $results);
    }

    private function regenerateClientSecret(string $connectionCode): void
    {
        $command = new RegenerateConnectionSecretCommand($connectionCode);
        $this->get(RegenerateConnectionSecretHandler::class)->handle($command);
    }

    private function regenerateUserPassword(string $connectionCode): void
    {
        $command = new RegenerateConnectionPasswordCommand($connectionCode);
        $this->get(RegenerateConnectionPasswordHandler::class)->handle($command);
    }

    private function findAConnection(string $connectionCode): ConnectionWithCredentials
    {
        $query = new FindAConnectionQuery($connectionCode);

        return $this->get(FindAConnectionHandler::class)->handle($query);
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration(): \Akeneo\Test\Integration\Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
