<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Infrastructure\Persistence\GetProductIdentifiersQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(GetProductIdentifiersQuery::class);
        $this->connection = self::getContainer()->get(Connection::class);
    }

    public function testItGetsPaginatedProductsUuids(): void
    {
        $owner = $this->createUser('owner');
        $ownerId = $owner->getId();

        $this->logAs($owner->getUserIdentifier());

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

    public function testItGetsPaginatedProductIdentifiersFromCatalogCriteriaHavingAScopeAndAChannel(): void
    {
        $owner = $this->createUser('owner');
        $this->logAs($owner->getUserIdentifier());

        $this->createCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c', 'Store US', $owner->getUserIdentifier());
        $this->enableCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        // use a text attribute filter when developped instead of completeness
        // to be clearer of what the point of this test
        $this->setCatalogProductSelection('db1079b6-f397-4a6a-bae4-8658e64ad47c', [
            [
                'field' => 'completeness',
                'operator' => '>',
                'value' => 99,
                'scope' => 'ecommerce',
                'locale' => 'en_US',
            ],
        ]);

        $this->createAttribute([
            'code' => 'a_localized_and_scopable_text',
            'type' => 'pim_catalog_text',
            'available_locales' => ['en_US'],
            'group' => 'other',
            'scopable' => true,
            'localizable' => true,
        ]);
        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['a_localized_and_scopable_text'],
            'attribute_requirements' => [
                'ecommerce' => ['a_localized_and_scopable_text'],
            ],
        ]);

        $ownerId = $owner->getId();
        $this->createProduct('red', [
            new SetEnabled(true),
            new SetFamily('a_family'),
            new SetCategories(['master']),
        ], $ownerId);
        $this->createProduct('blue', [
            new SetEnabled(true),
            new SetFamily('a_family'),
            new SetCategories(['master']),
            new SetTextValue('a_localized_and_scopable_text', 'ecommerce', 'en_US', 'optionA'),
        ], $ownerId);

        $result = $this->query->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c', null, 2);

        $this->assertEquals(['blue'], $result);
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
