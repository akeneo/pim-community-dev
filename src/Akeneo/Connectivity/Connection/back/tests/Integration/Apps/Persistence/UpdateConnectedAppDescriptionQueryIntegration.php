<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\FindOneConnectedAppByIdQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\UpdateConnectedAppDescriptionQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectedAppLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateConnectedAppDescriptionQueryIntegration extends TestCase
{
    private ConnectedAppLoader $connectedAppLoader;
    private UpdateConnectedAppDescriptionQuery $query;
    private FindOneConnectedAppByIdQuery $findOneConnectedAppByIdQuery;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connectedAppLoader = $this->get('akeneo_connectivity.connection.fixtures.connected_app_loader');
        $this->query = $this->get(UpdateConnectedAppDescriptionQuery::class);
        $this->findOneConnectedAppByIdQuery = $this->get(FindOneConnectedAppByIdQuery::class);
    }

    public function test_it_updates_the_description_of_a_connected_app(): void
    {
        $this->connectedAppLoader->createConnectedAppWithUserAndTokens(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'magento',
        );

        $this->assertConnectedAppHasValues('2677e764-f852-4956-bf9b-1a1ec1b0d145', [
            'name' => 'magento',
            'logo' => 'http://example.com/logo.png',
            'author' => 'Akeneo',
            'categories' => ['ecommerce'],
            'certified' => false,
            'partner' => null,
        ]);

        $this->updateConnectedAppDescription('2677e764-f852-4956-bf9b-1a1ec1b0d145', [
            'name' => 'MAGENTO',
            'logo' => 'http://example.com/LOGO.png',
            'author' => 'AKENEO',
            'categories' => ['ECOMMERCE'],
            'certified' => true,
            'partner' => 'AKENEO',
        ]);

        $this->assertConnectedAppHasValues('2677e764-f852-4956-bf9b-1a1ec1b0d145', [
            'name' => 'MAGENTO',
            'logo' => 'http://example.com/LOGO.png',
            'author' => 'AKENEO',
            'categories' => ['ECOMMERCE'],
            'certified' => true,
            'partner' => 'AKENEO',
        ]);
    }

    private function updateConnectedAppDescription(string $id, array $values): void
    {
        $connectedApp = $this->findOneConnectedAppByIdQuery->execute($id);

        $updatedConnectedApp = $connectedApp->withUpdatedDescription(
            $values['name'],
            $values['logo'],
            $values['author'],
            $values['categories'],
            $values['certified'],
            $values['partner'],
        );

        $this->query->execute($updatedConnectedApp);
    }

    private function assertConnectedAppHasValues(string $id, array $expected): void
    {
        $connectedApp = $this->findOneConnectedAppByIdQuery->execute($id);
        $normalizedConnectedApp = $connectedApp->normalize();
        $actual = \array_combine(\array_keys($expected), \array_intersect_key($normalizedConnectedApp, $expected));

        Assert::assertSame($expected, $actual);
    }
}
