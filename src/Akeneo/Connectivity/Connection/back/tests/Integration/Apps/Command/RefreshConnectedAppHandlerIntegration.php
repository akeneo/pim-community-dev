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
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RefreshConnectedAppHandlerIntegration extends TestCase
{
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
                'name' => 'MAGENTO',
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

        $this->refreshConnectedApp('2677e764-f852-4956-bf9b-1a1ec1b0d145');

        $this->assertConnectedAppHasValues('2677e764-f852-4956-bf9b-1a1ec1b0d145', [
            'name' => 'MAGENTO',
            'logo' => 'http://example.com/LOGO.png',
            'author' => 'AKENEO',
            'categories' => ['ECOMMERCE'],
            'certified' => true,
            'partner' => 'AKENEO',
        ]);
    }

    private function refreshConnectedApp($id): void
    {
        $this->refreshConnectedAppHandler->handle(new RefreshConnectedAppCommand($id));
    }

    private function assertConnectedAppHasValues($id, array $expected): void
    {
        $connectedApp = $this->findOneConnectedAppByIdQuery->execute($id);
        $normalizedConnectedApp = $connectedApp->normalize();
        $actual = \array_combine(\array_keys($expected), \array_intersect_key($normalizedConnectedApp, $expected));

        Assert::assertSame($expected, $actual);
    }
}
