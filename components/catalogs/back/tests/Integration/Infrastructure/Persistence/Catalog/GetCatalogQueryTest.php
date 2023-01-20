<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogQuery
 */
class GetCatalogQueryTest extends IntegrationTestCase
{
    private ?GetCatalogQuery $query;
    private ?Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->connection = self::getContainer()->get(Connection::class);
        $this->query = self::getContainer()->get(GetCatalogQuery::class);
    }

    public function testItGetsCatalog(): void
    {
        $this->createUser('test');
        $id = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';

        $this->createCatalog($id, 'Store US', 'test', isEnabled: false);

        $this->setCatalogProductValueFilters(
            $id,
            ['channels' => ['ecommerce', 'print']],
        );

        $result = $this->query->execute($id);

        $expected = new Catalog(
            $id,
            'Store US',
            'test',
            false,
            [
                [
                    'field' => 'enabled',
                    'operator' => '=',
                    'value' => true,
                ],
            ],
            ['channels' => ['ecommerce', 'print']],
            [],
        );

        $this->assertEquals($expected, $result);
    }

    public function testItThrowsWhenCatalogDoesNotExist(): void
    {
        $this->expectException(CatalogNotFoundException::class);

        $this->query->execute('017c3d69-5c7d-4cd9-9d19-4ffe856026a3');
    }

    public function testItThrowsWhenProductSelectionCriteriaIsInvalid(): void
    {
        $this->createUser('test');
        $id = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';

        $this->createCatalog($id, 'Store US', 'test');
        $this->setInvalidCatalogProductSelectionCriteria($id);

        $this->setCatalogProductValueFilters(
            $id,
            ['channels' => ['ecommerce', 'print']],
        );

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Invalid JSON in product_selection_criteria column');

        $this->query->execute($id);
    }

    public function testItThrowsWhenProductValueFiltersIsInvalid(): void
    {
        $this->createUser('test');
        $id = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';

        $this->createCatalog($id, 'Store US', 'test');
        $this->setInvalidCatalogProductValueFilters($id);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Invalid JSON in product_value_filters column');

        $this->query->execute($id);
    }

    private function setInvalidCatalogProductSelectionCriteria(string $id): void
    {
        $this->connection->executeQuery(
            'UPDATE akeneo_catalog SET product_selection_criteria = :criteria WHERE id = :id',
            [
                'id' => Uuid::fromString($id)->getBytes(),
                'criteria' => 'invalid_product_selection_criteria',
            ],
            [
                'criteria' => Types::JSON,
            ],
        );
    }

    private function setInvalidCatalogProductValueFilters(string $id): void
    {
        $this->connection->executeQuery(
            'UPDATE akeneo_catalog SET product_value_filters = :filters WHERE id = :id',
            [
                'id' => Uuid::fromString($id)->getBytes(),
                'filters' => 'invalid_product_value_filters',
            ],
            [
                'filters' => Types::JSON,
            ],
        );
    }
}
