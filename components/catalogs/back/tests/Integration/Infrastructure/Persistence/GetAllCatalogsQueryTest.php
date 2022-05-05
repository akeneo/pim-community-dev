<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Domain\Model\Catalog;
use Akeneo\Catalogs\Infrastructure\Persistence\GetAllCatalogsQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAllCatalogsQueryTest extends IntegrationTestCase
{
    private GetAllCatalogsQuery $query;
    private Connection $connection;

    public function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->connection = self::getContainer()->get(Connection::class);
        $this->query = self::getContainer()->get(GetAllCatalogsQuery::class);
    }

    public function testItReturnsAllCalalogs(): void
    {
        $this->insertCatalog([
            'id' => '32d56cf2-cadc-403e-b39b-6f6277a65220',
            'name' => 'Store US',
        ]);
        $this->insertCatalog([
            'id' => 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'name' => 'Store FR',
        ]);

        $catalogs = $this->query->execute();

        $this->assertEquals([
            Catalog::fromSerialized([
                'id' => '32d56cf2-cadc-403e-b39b-6f6277a65220',
                'name' => 'Store US',
            ]),
            Catalog::fromSerialized([
                'id' => 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
                'name' => 'Store FR',
            ]),
        ], $catalogs);
    }

    private function insertCatalog(array $values): void
    {
        $this->connection->insert(
            'akeneo_catalog',
            \array_merge($values, [
                'id' => Uuid::fromString($values['id'])->getBytes(),
            ])
        );
    }
}
