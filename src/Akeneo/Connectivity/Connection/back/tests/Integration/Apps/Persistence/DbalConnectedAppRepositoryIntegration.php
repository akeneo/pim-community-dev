<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\DbalConnectedAppRepository;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalConnectedAppRepositoryIntegration extends TestCase
{
    private DbalConnectedAppRepository $repository;
    private Connection $connection;
    private ConnectionLoader $connectionLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->get(DbalConnectedAppRepository::class);
        $this->connection = $this->get('database_connection');
        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
    }

    public function test_it_persist_an_app(): void
    {
        $this->connectionLoader->createConnection('bynder', 'Bynder', FlowType::OTHER, false);

        $this->repository->create(
            new ConnectedApp(
                '86d603e6-ec67-45fa-bd79-aa8b2f649e12',
                'my app',
                ['foo', 'bar'],
                'bynder',
                'app logo',
                'app author',
                ['e-commerce'],
                false,
                'akeneo'
            )
        );

        $row = $this->fetchApp('86d603e6-ec67-45fa-bd79-aa8b2f649e12');

        Assert::assertSame([
            'id' => '86d603e6-ec67-45fa-bd79-aa8b2f649e12',
            'name' => 'my app',
            'logo' => 'app logo',
            'author' => 'app author',
            'partner' => 'akeneo',
            'categories' => '["e-commerce"]',
            'certified' => '0',
            'connection_code' => 'bynder',
            'scopes' => '["foo", "bar"]',
        ], $row);
    }

    public function test_it_can_retrieve_an_app(): void
    {
        $this->connectionLoader->createConnection('bynder', 'Bynder', FlowType::OTHER, false);

        $createdApp = new ConnectedApp(
            '86d603e6-ec67-45fa-bd79-aa8b2f649e12',
            'my app',
            ['foo', 'bar'],
            'bynder',
            'app logo',
            'app author',
            ['e-commerce'],
            false,
            'akeneo'
        );
        $this->repository->create($createdApp);

        $retrievedApp = $this->repository->findOneById('86d603e6-ec67-45fa-bd79-aa8b2f649e12');

        Assert::assertEquals(serialize($createdApp), serialize($retrievedApp));
    }

    public function test_it_finds_all_ordered_by_name()
    {
        $this->connectionLoader->createConnection('connectionCodeB', 'Connector B', FlowType::DATA_DESTINATION, false);
        $createdAppB = new ConnectedApp(
            '2677e764-f852-4956-bf9b-1a1ec1b0d145',
            'App B',
            ['scope B1', 'scope B2'],
            'connectionCodeB',
            'http://www.example.com/path/to/logo/b',
            'author B',
            ['category B1'],
            true,
            null
        );
        $this->repository->create($createdAppB);

        $this->connectionLoader->createConnection('connectionCodeA', 'Connector A', FlowType::DATA_DESTINATION, false);
        $createdAppA = new ConnectedApp(
            '0dfce574-2238-4b13-b8cc-8d257ce7645b',
            'App A',
            ['scope A1'],
            'connectionCodeA',
            'http://www.example.com/path/to/logo/a',
            'author A',
            ['category A1', 'category A2'],
            false,
            'partner A'
        );
        $this->repository->create($createdAppA);

        $connectedApps = $this->repository->findAll();

        Assert::assertEquals(serialize($createdAppA), serialize($connectedApps[0]));
        Assert::assertEquals(serialize($createdAppB), serialize($connectedApps[1]));
    }

    private function fetchApp(string $id): ?array
    {
        $query = <<<SQL
SELECT *
FROM akeneo_connectivity_connected_app
WHERE id = :id
SQL;

        $row = $this->connection->fetchAssoc($query, [
            'id' => $id,
        ]);

        return $row ?: null;
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
