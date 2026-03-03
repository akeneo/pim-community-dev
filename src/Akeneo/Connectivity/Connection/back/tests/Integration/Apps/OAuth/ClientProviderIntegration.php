<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Apps\OAuth;

use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProvider;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use Doctrine\DBAL\Connection;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use OAuth2\OAuth2;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ClientProviderIntegration extends TestCase
{
    private Connection $connection;
    private ClientProvider $clientProvider;
    private ClientManagerInterface $clientManager;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->clientProvider = $this->get(ClientProvider::class);
        $this->clientManager = $this->get('fos_oauth_server.client_manager.default');
    }

    public function test_it_creates_a_client_if_there_is_none(): void
    {
        $app = $this->createApp([
            'id' => '97bd9bb8-c86c-4faa-948a-4b4decccfd62',
        ]);

        $this->assertThereIsNoClientForApp($app);

        $client = $this->clientProvider->findOrCreateClient($app);

        $this->assertThereIsOneClientForApp($app);
        $this->assertClientIsValid($app, $client);
    }

    public function test_it_does_not_creates_a_client_if_there_is_already_one(): void
    {
        $app = $this->createApp([
            'id' => '97bd9bb8-c86c-4faa-948a-4b4decccfd62',
        ]);

        $firstClient = $this->clientProvider->findOrCreateClient($app);

        $this->assertThereIsOneClientForApp($app);
        $this->assertClientIsValid($app, $firstClient);

        $secondClient = $this->clientProvider->findOrCreateClient($app);

        $this->assertThereIsOneClientForApp($app);
        $this->assertClientIsValid($app, $secondClient);

        $this->assertSame($firstClient, $secondClient);
    }

    public function test_it_retrieves_a_client_by_id(): void
    {
        $app = $this->createApp([
            'id' => '97bd9bb8-c86c-4faa-948a-4b4decccfd62',
        ]);
        $this->createClient($app->getId(), $app->getCallbackUrl());

        $client = $this->clientProvider->findClientByAppId('97bd9bb8-c86c-4faa-948a-4b4decccfd62');

        $this->assertNotNull($client);
        $this->assertClientIsValid($app, $client);
    }

    private function assertThereIsNoClientForApp(App $app): void
    {
        $sql = 'SELECT id FROM pim_api_client WHERE marketplace_public_app_id = :id';
        $results = $this->connection->executeQuery($sql, [
            'id' => $app->getId(),
        ])->fetchAllAssociative();
        $this->assertEmpty($results);
    }

    private function assertThereIsOneClientForApp(App $app): void
    {
        $sql = 'SELECT id FROM pim_api_client WHERE marketplace_public_app_id = :id';
        $results = $this->connection->executeQuery($sql, [
            'id' => $app->getId(),
        ])->fetchAllAssociative();
        $this->assertCount(1, $results);
    }

    private function assertClientIsValid(App $app, Client $client): void
    {
        Assert::assertNotNull($client->getId(), 'Client not persisted');
        Assert::assertEquals([OAuth2::GRANT_TYPE_AUTH_CODE], $client->getAllowedGrantTypes());
        Assert::assertEquals($app->getId(), $client->getMarketplacePublicAppId());
        Assert::assertContains($app->getCallbackUrl(), $client->getRedirectUris());
    }

    private function createApp(array $data): App
    {
        return App::fromWebMarketplaceValues(
            \array_merge([
                'name' => 'name',
                'logo' => 'logo',
                'author' => 'author',
                'url' => 'url',
                'categories' => ['category_1', 'category_2'],
                'activate_url' => 'activate_url',
                'callback_url' => 'callback_url',
            ], $data)
        );
    }

    private function createClient(string $appId, string $callbackUrl): Client
    {
        /** @var Client $client */
        $client = $this->clientManager->createClient();

        $client->setRedirectUris([$callbackUrl]);
        $client->setAllowedGrantTypes([OAuth2::GRANT_TYPE_AUTH_CODE]);
        $client->setMarketplacePublicAppId($appId);

        $this->clientManager->updateClient($client);

        return $client;
    }

    private function countExistingClients(): int
    {
        $sql = "SELECT COUNT(id) FROM pim_api_client";

        return (int) $this->connection->executeQuery($sql)->fetchOne();
    }
}
