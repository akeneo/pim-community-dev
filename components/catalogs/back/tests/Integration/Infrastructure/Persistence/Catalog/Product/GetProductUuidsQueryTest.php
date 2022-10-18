<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductUuidsQueryInterface;
use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductUuidsQueryTest extends IntegrationTestCase
{
    private ?Connection $connection;
    private ?GetCatalogQueryInterface $getCatalogQuery;
    private ?GetProductUuidsQueryInterface $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->connection = self::getContainer()->get(Connection::class);
        $this->getCatalogQuery = self::getContainer()->get(GetCatalogQueryInterface::class);
        $this->query = self::getContainer()->get(GetProductUuidsQueryInterface::class);
    }

    public function testItGetsMatchingProductsUuids(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c', 'Store US', 'owner');
        $this->enableCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c');
        $this->setCatalogProductSelection('db1079b6-f397-4a6a-bae4-8658e64ad47c', [
            [
                'field' => 'enabled',
                'operator' => Operator::EQUALS,
                'value' => true,
            ],
        ]);
        $this->createProduct('tshirt-blue', [new SetEnabled(true)]);
        $this->createProduct('tshirt-green', [new SetEnabled(true)]);
        $this->createProduct('tshirt-red', [new SetEnabled(false)]);

        $uuids = $this->getProductIdentifierToUuidMapping();

        $catalog = $this->getCatalogQuery->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = $this->query->execute($catalog);

        $this->assertEquals([
            $uuids['tshirt-blue'],
            $uuids['tshirt-green'],
        ], $result);
    }

    public function testItGetsMatchingProductsUuidsUsingSearchAfterAndLimit(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c', 'Store US', 'owner');
        $this->enableCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c');
        $this->setCatalogProductSelection('db1079b6-f397-4a6a-bae4-8658e64ad47c', [
            [
                'field' => 'enabled',
                'operator' => Operator::EQUALS,
                'value' => true,
            ],
        ]);
        $this->createProduct('tshirt-blue', [new SetEnabled(true)]);
        $this->createProduct('tshirt-green', [new SetEnabled(true)]);
        $this->createProduct('tshirt-red', [new SetEnabled(true)]);
        $this->createProduct('tshirt-yellow', [new SetEnabled(true)]);

        $uuids = $this->getProductIdentifierToUuidMapping();

        $catalog = $this->getCatalogQuery->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = $this->query->execute($catalog, $uuids['tshirt-green'], 1);

        $this->assertEquals([
            $uuids['tshirt-red'],
        ], $result);
    }

    public function testItThrowsWhenTheSearchAfterIsInvalid(): void
    {
        $this->createUser('owner');
        $this->createCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c', 'Store US', 'owner');
        $this->enableCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c');
        $this->setCatalogProductSelection('db1079b6-f397-4a6a-bae4-8658e64ad47c', [
            [
                'field' => 'enabled',
                'operator' => Operator::EQUALS,
                'value' => true,
            ],
        ]);

        $this->expectException(\InvalidArgumentException::class);

        $catalog = $this->getCatalogQuery->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $this->query->execute($catalog, 'invalid_search_after');
    }

    public function testItGetsMatchingProductsUuidsWhenUsingScopableAndLocalizableCriterion(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createChannel('print', ['en_US', 'fr_FR']);
        $this->createChannel('mobile', ['en_US', 'fr_FR']);
        $this->createAttribute([
            'code' => 'name',
            'type' => 'pim_catalog_text',
            'scopable' => true,
            'localizable' => true,
        ]);
        $this->createCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c', 'Store US', 'owner');
        $this->enableCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c');
        $this->setCatalogProductSelection('db1079b6-f397-4a6a-bae4-8658e64ad47c', [
            [
                'field' => 'name',
                'operator' => Operator::EQUALS,
                'value' => 'Bleu clair',
                'scope' => 'print',
                'locale' => 'fr_FR',
            ],
        ]);
        $this->createProduct('tshirt-blue', [
            new SetTextValue('name', 'mobile', 'en_US', 'Blue'),
            new SetTextValue('name', 'print', 'en_US', 'Light blue'),
            new SetTextValue('name', 'print', 'fr_FR', 'Bleu clair'),
        ]);
        $this->createProduct('wrong_blue', [
            new SetTextValue('name', 'mobile', 'fr_FR', 'Bleu clair'), // wrong channel
            new SetTextValue('name', 'print', 'en_US', 'Bleu clair'), // wrong locale
        ]);

        $uuids = $this->getProductIdentifierToUuidMapping();

        $catalog = $this->getCatalogQuery->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = $this->query->execute($catalog);

        $this->assertEquals([
            $uuids['tshirt-blue'],
        ], $result);
    }

    public function testItGetsMatchingProductsUuidsUsingUpdatedAfter(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c', 'Store US', 'owner');
        $this->enableCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c');
        $this->setCatalogProductSelection('db1079b6-f397-4a6a-bae4-8658e64ad47c', [
            [
                'field' => 'enabled',
                'operator' => Operator::EQUALS,
                'value' => true,
            ],
        ]);

        $this->clock->set(new \DateTimeImmutable('2022-09-01T15:30:00+00:00'));
        $this->createProduct('tshirt-blue', [new SetEnabled(true)]);

        $this->clock->set(new \DateTimeImmutable('2022-09-01T15:40:00+00:00'));
        $this->createProduct('tshirt-green', [new SetEnabled(true)]);

        $uuids = $this->getProductIdentifierToUuidMapping();

        $catalog = $this->getCatalogQuery->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = $this->query->execute($catalog, null, 100, '2022-09-01T17:35:00+02:00');

        $this->assertEquals([
            $uuids['tshirt-green'],
        ], $result);
    }

    public function testItGetsMatchingProductsUuidsUsingUpdatedBefore(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c', 'Store US', 'owner');
        $this->enableCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c');
        $this->setCatalogProductSelection('db1079b6-f397-4a6a-bae4-8658e64ad47c', [
            [
                'field' => 'enabled',
                'operator' => Operator::EQUALS,
                'value' => true,
            ],
        ]);

        $this->clock->set(new \DateTimeImmutable('2022-09-01T15:30:00+00:00'));
        $this->createProduct('tshirt-blue', [new SetEnabled(true)]);

        $this->clock->set(new \DateTimeImmutable('2022-09-01T15:40:00+00:00'));
        $this->createProduct('tshirt-green', [new SetEnabled(true)]);

        $uuids = $this->getProductIdentifierToUuidMapping();

        $catalog = $this->getCatalogQuery->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = $this->query->execute($catalog, null, 100, null, '2022-09-01T17:35:00+02:00');

        $this->assertEquals([
            $uuids['tshirt-blue'],
        ], $result);
    }

    public function testItGetsMatchingProductsUuidsUsingUpdatedBeforeAndUpdatedAfter(): void
    {
        $this->createUser('owner');
        $this->logAs('owner');

        $this->createCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c', 'Store US', 'owner');
        $this->enableCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c');
        $this->setCatalogProductSelection('db1079b6-f397-4a6a-bae4-8658e64ad47c', [
            [
                'field' => 'enabled',
                'operator' => Operator::EQUALS,
                'value' => true,
            ],
        ]);

        $this->clock->set(new \DateTimeImmutable('2022-09-01T15:30:00+00:00'));
        $this->createProduct('tshirt-blue', [new SetEnabled(true)]);

        $this->clock->set(new \DateTimeImmutable('2022-09-01T15:40:00+00:00'));
        $this->createProduct('tshirt-green', [new SetEnabled(true)]);

        $uuids = $this->getProductIdentifierToUuidMapping();

        $catalog = $this->getCatalogQuery->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $result = $this->query->execute($catalog, null, 100, '2022-09-01T17:35:00+02:00', '2022-09-01T17:45:00+02:00');

        $this->assertEquals([
            $uuids['tshirt-green'],
        ], $result);
    }

    /**
     * @return array<string, string>
     */
    private function getProductIdentifierToUuidMapping(): array
    {
        $sql = <<<SQL
            SELECT BIN_TO_UUID(uuid) AS uuid, identifier
            FROM pim_catalog_product
        SQL;

        $rows = $this->connection->fetchAllAssociative($sql);
        $map = [];

        foreach ($rows as $row) {
            $map[$row['identifier']] = $row['uuid'];
        }

        return $map;
    }
}
