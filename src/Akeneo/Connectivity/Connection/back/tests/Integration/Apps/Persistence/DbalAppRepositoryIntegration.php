<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\DbalConnectedAppRepository;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\ConnectionLoader;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DbalAppRepositoryIntegration extends TestCase
{
    private DbalConnectedAppRepository $appRepository;
    private Connection $connection;
    private ConnectionLoader $connectionLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->appRepository = $this->get(DbalConnectedAppRepository::class);
        $this->connection = $this->get('database_connection');
        $this->connectionLoader = $this->get('akeneo_connectivity.connection.fixtures.connection_loader');
    }

    public function test_it_persist_an_app(): void
    {
        $this->connectionLoader->createConnection('bynder', 'Bynder', FlowType::OTHER, false);

        $this->appRepository->create(
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

        $this->assertSame([
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
        $this->appRepository->create($createdApp);

        $retrievedApp = $this->appRepository->findOneById('86d603e6-ec67-45fa-bd79-aa8b2f649e12');

        $this->assertEquals(serialize($createdApp), serialize($retrievedApp));
    }

    private function fetchApp(string $id): ?array
    {
        $query = <<<SQL
SELECT *
FROM akeneo_connectivity_app
WHERE id = :id
SQL;

        $row = $this->connection->fetchAssoc($query, [
            'id' => $id,
        ]);

        return $row ?: null;
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
