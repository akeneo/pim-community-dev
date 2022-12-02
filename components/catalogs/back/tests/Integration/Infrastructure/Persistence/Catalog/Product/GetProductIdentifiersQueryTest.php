<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogQuery;
use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product\GetProductIdentifiersQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductIdentifiersQueryTest extends IntegrationTestCase
{
    private ?GetProductIdentifiersQuery $query;
    private ?Connection $connection;
    private ?GetCatalogQuery $getCatalogQuery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(GetProductIdentifiersQuery::class);
        $this->connection = self::getContainer()->get(Connection::class);
        $this->getCatalogQuery = self::getContainer()->get(GetCatalogQuery::class);
    }

    public function testItGetsPaginatedProductsUuids(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $catalogId = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';

        $this->createCatalog($catalogId, 'Store US', 'owner');

        $this->createProduct('tshirt-blue', [new SetEnabled(true)]);
        $this->createProduct('tshirt-green', [new SetEnabled(true)]);
        $this->createProduct('tshirt-red', [new SetEnabled(true)]);
        $this->createProduct('tshirt-yellow', [new SetEnabled(false)]);

        $catalog = $this->getCatalogQuery->execute($catalogId);

        $expected = ['tshirt-blue', 'tshirt-green'];
        $result = $this->query->execute($catalog, null, 2);

        $this->assertEquals($expected, $result);

        $searchAfter = $this->findProductUuid('tshirt-green');
        $expected = ['tshirt-red'];
        $result = $this->query->execute($catalog, $searchAfter, 2);

        $this->assertEquals($expected, $result);
    }

    public function testItThrowsWhenTheSearchAfterIsInvalid(): void
    {
        $this->createUser('owner');

        $catalogId = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->createCatalog($catalogId, 'Store US', 'owner');

        $catalog = $this->getCatalogQuery->execute($catalogId);

        $this->expectException(\InvalidArgumentException::class);

        $this->query->execute($catalog, 'invalid_search_after');
    }

    public function testItGetsPaginatedProductIdentifiersFromCatalogCriteriaWithScopeAndChannel(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createChannel('print', ['en_US', 'fr_FR']);
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'scopable' => true,
            'localizable' => true,
        ]);
        $this->createProduct('tshirt-blue', [
            new SetTextValue('name', 'ecommerce', 'en_US', 'Blue'),
            new SetTextValue('name', 'print', 'en_US', 'Light blue'),
            new SetTextValue('name', 'print', 'fr_FR', 'Bleu clair'),
        ]);

        $catalogId = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->createCatalog($catalogId, 'Store US', 'owner');
        $this->setCatalogProductSelection($catalogId, [
            [
                'field' => 'name',
                'operator' => Operator::EQUALS,
                'value' => 'Bleu clair',
                'scope' => 'print',
                'locale' => 'fr_FR',
            ],
        ]);

        $catalog = $this->getCatalogQuery->execute($catalogId);

        $result = $this->query->execute($catalog);

        $this->assertEquals(['tshirt-blue'], $result);
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
