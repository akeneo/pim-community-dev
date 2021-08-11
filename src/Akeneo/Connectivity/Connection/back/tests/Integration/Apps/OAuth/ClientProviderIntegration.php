<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Apps\OAuth;

use Akeneo\Connectivity\Connection\Domain\Marketplace\Model\App;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\OAuth\ClientProvider;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ApiBundle\Entity\Client;
use Doctrine\DBAL\Connection;
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

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->clientProvider = $this->get('akeneo_connectivity.connection.service.apps.client_provider');
    }

    public function test_client_provider_creates_or_finds_a_client(): void
    {
        $beforeCount = $this->countExistingClients();
        $app = $this->getDummyApp('app1');

        $client = $this->clientProvider->findOrCreateClient($app);

        $afterCount = $this->countExistingClients();
        Assert::assertEquals($beforeCount + 1, $afterCount, ' No client created');
        $this->assertValidClient($app, $client);

        $clientBis = $this->clientProvider->findOrCreateClient($app);

        Assert::assertEquals($beforeCount + 1, $afterCount, ' Duplicate client created');
        Assert::assertSame($client, $clientBis, 'Different client for same app');
        $this->assertValidClient($app, $clientBis);

        $app2 = $this->getDummyApp('app2');
        $client2 = $this->clientProvider->findOrCreateClient($app2);

        $afterApp2Count = $this->countExistingClients();
        Assert::assertEquals($beforeCount + 2, $afterApp2Count, ' No client created');
        $this->assertValidClient($app2, $client2);
    }

    private function assertValidClient(App $app, Client $client): void
    {
        Assert::assertNotNull($client->getId(), 'Client not persisted');
        Assert::assertEquals([OAuth2::GRANT_TYPE_AUTH_CODE], $client->getAllowedGrantTypes(), 'Client has invalid grand types');
        Assert::assertEquals($app->getId(), $client->getMarketplacePublicAppId(), 'Client has invalid grand types');
        Assert::assertContains($app->getCallbackUrl(), $client->getRedirectUris(), 'Client missing redirect uri');
    }

    private function getDummyApp(string $id): App
    {
        return App::fromWebMarketplaceValues([
            'id' => $id,
            'name' => "$id name",
            'logo' => "$id logo",
            'author' => "$id author",
            'url' => "$id url",
            'categories' => ["$id category_1", "$id category_2"],
            'activate_url' => "$id activate_url",
            'callback_url' => "$id callback_url",
        ]);
    }

    private function countExistingClients(): int
    {
        $sql = "SELECT COUNT(id) FROM pim_api_client";

        return (int) $this->connection->executeQuery($sql)->fetchColumn();
    }
}
