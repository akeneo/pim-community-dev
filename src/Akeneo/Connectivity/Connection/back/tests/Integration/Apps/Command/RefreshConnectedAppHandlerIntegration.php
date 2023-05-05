<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\RefreshConnectedAppCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\RefreshConnectedAppHandler;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\FindOneConnectedAppByIdQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Marketplace\WebMarketplaceApi;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Connectivity\Connection\Tests\Integration\Mock\FakeWebMarketplaceApi;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RefreshConnectedAppHandlerIntegration extends TestCase
{
    private Connection $connection;
    private ConnectedAppLoader $connectedAppLoader;
    private RefreshConnectedAppHandler $refreshConnectedAppHandler;
    private FindOneConnectedAppByIdQuery $findOneConnectedAppByIdQuery;
    private FakeWebMarketplaceApi $webMarketplaceApi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->refreshConnectedAppHandler = $this->get(RefreshConnectedAppHandler::class);
        $this->findOneConnectedAppByIdQuery = $this->get(FindOneConnectedAppByIdQuery::class);
        $this->webMarketplaceApi = $this->get(WebMarketplaceApi::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_refreshes_a_connected_app(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'magento',
        );
        $this->webMarketplaceApi->setApps([
            [
                'id' => '2677e764-f852-4956-bf9b-1a1ec1b0d145',
                'name' => 'MAGENTO 2',
                'logo' => 'http://example.com/LOGO.png',
                'author' => 'AKENEO',
                'partner' => 'AKENEO',
                'description' => '',
                'url' => '',
                'categories' => [
                    'ECOMMERCE',
                ],
                'certified' => true,
                'activate_url' => 'http://example.com/activate',
                'callback_url' => 'http://example.com/callback',
            ],
        ]);

        $this->assertConnectedAppHasValues('2677e764-f852-4956-bf9b-1a1ec1b0d145', [
            'name' => 'magento',
            'logo' => 'http://example.com/logo.png',
            'author' => 'Akeneo',
            'categories' => ['ecommerce'],
            'certified' => false,
            'partner' => null,
        ]);

        $this->assertEquals(
            'magento',
            $this->findConnectedAppUserFirstname('2677e764-f852-4956-bf9b-1a1ec1b0d145')
        );

        $this->refreshConnectedApp('2677e764-f852-4956-bf9b-1a1ec1b0d145');

        $this->assertConnectedAppHasValues('2677e764-f852-4956-bf9b-1a1ec1b0d145', [
            'name' => 'MAGENTO 2',
            'logo' => 'http://example.com/LOGO.png',
            'author' => 'AKENEO',
            'categories' => ['ECOMMERCE'],
            'certified' => true,
            'partner' => 'AKENEO',
        ]);

        $this->assertEquals(
            'MAGENTO 2',
            $this->findConnectedAppUserFirstname('2677e764-f852-4956-bf9b-1a1ec1b0d145')
        );
    }

    private function refreshConnectedApp(string $id): void
    {
        $this->refreshConnectedAppHandler->handle(new RefreshConnectedAppCommand($id));
    }

    private function assertConnectedAppHasValues(string $id, array $expected): void
    {
        $connectedApp = $this->findOneConnectedAppByIdQuery->execute($id);
        $normalizedConnectedApp = $connectedApp->normalize();
        $actual = \array_combine(\array_keys($expected), \array_intersect_key($normalizedConnectedApp, $expected));

        Assert::assertSame($expected, $actual);
    }

    private function findConnectedAppUserFirstname(string $id): string
    {
        $query = <<<SQL
SELECT oro_user.first_name
FROM akeneo_connectivity_connected_app
JOIN akeneo_connectivity_connection ON akeneo_connectivity_connection.code = akeneo_connectivity_connected_app.connection_code
JOIN oro_user ON oro_user.id = akeneo_connectivity_connection.user_id
WHERE akeneo_connectivity_connected_app.id = :id
SQL;

        return $this->connection->fetchOne($query, [
            'id' => $id,
        ]);
    }
}
