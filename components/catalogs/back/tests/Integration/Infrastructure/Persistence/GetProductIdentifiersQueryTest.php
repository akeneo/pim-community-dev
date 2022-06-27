<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Infrastructure\Persistence\GetProductIdentifiersQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductIdentifiersQueryTest extends IntegrationTestCase
{
    private ?GetProductIdentifiersQuery $query;
    private ?Connection $connection;

    public function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(GetProductIdentifiersQuery::class);
        $this->connection = self::getContainer()->get(Connection::class);
    }

    public function testItGetsPaginatedProductsUuids(): void
    {
        $ownerId = $this->createUser('owner')->getId();
        $this->createCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c', 'Store US', 'owner');
        $this->enableCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c');
        $this->createProduct('blue', [new SetEnabled(true)], $ownerId);
        $this->createProduct('green', [new SetEnabled(true)], $ownerId);
        $this->createProduct('red', [new SetEnabled(true)], $ownerId);
        $this->createProduct('yellow', [new SetEnabled(false)], $ownerId);

        $expected = ['blue', 'green'];
        $result = $this->query->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c', null, 2);

        $this->assertEquals($expected, $result);

        $searchAfter = $this->findProductUuid('green');
        $expected = ['red'];
        $result = $this->query->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c', $searchAfter, 2);

        $this->assertEquals($expected, $result);
    }

    public function testItThrowsWhenTheSearchAfterIsInvalid(): void
    {
        $this->createUser('owner');
        $this->createCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c', 'Store US', 'owner');

        $this->expectException(\InvalidArgumentException::class);

        $this->query->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c', 'invalid_search_after');
    }

    private function findProductUuid(string $identifier): string
    {
        $sql = <<<SQL
            SELECT BIN_TO_UUID(uuid)
            FROM pim_catalog_product
            WHERE identifier = :identifier
        SQL;

        return $this->connection->fetchOne($sql, [
            'identifier' => $identifier,
        ]);
    }
}
