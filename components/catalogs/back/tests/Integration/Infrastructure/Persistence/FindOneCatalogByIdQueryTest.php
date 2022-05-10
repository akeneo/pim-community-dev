<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Domain\Model\Catalog;
use Akeneo\Catalogs\Infrastructure\Persistence\FindOneCatalogByIdQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindOneCatalogByIdQueryTest extends IntegrationTestCase
{
    private ?FindOneCatalogByIdQuery $query;
    private ?Connection $connection;

    public function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->connection = self::getContainer()->get(Connection::class);
        $this->query = self::getContainer()->get(FindOneCatalogByIdQuery::class);
    }

    public function testItFindsTheCatalog(): void
    {
        $owner = $this->createUser('test');
        $id = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->insertCatalog([
            'id' => $id,
            'name' => 'Store US',
            'owner_id' => $owner->getId(),
        ]);

        $result = $this->query->execute($id);
        $expected = new Catalog($id, 'Store US', $owner->getId());

        $this->assertEquals($expected, $result);
    }

    public function testItReturnsNullIfUnknownId(): void
    {
        $result = $this->query->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $this->assertNull($result);
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
