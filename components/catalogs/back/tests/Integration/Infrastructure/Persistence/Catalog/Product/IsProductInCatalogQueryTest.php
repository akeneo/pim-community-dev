<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog\Product;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product\IsProductInCatalogQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Ramsey\Uuid\Uuid;

/**
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Catalog\Product\IsProductInCatalogQuery
 */
class IsProductInCatalogQueryTest extends IntegrationTestCase
{
    private IsProductInCatalogQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = self::getContainer()->get(IsProductInCatalogQuery::class);

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItReturnsTrueWhenTheProductIsInTheCatalog(): void
    {
        $this->createUser('shopifi');
        $this->logAs('shopifi');
        $catalog = $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'shopifi',
            enabled: true,
            productSelection: [
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ],
        );
        $this->createProduct(Uuid::fromString('00380587-3893-46e6-a8c2-8fee6404cc9e'), [new SetEnabled(true)]);

        $result = $this->query->execute($catalog, '00380587-3893-46e6-a8c2-8fee6404cc9e');

        $this->assertTrue($result);
    }

    public function testItReturnsFalseWhenTheProductIsNotInTheCatalog(): void
    {
        $this->createUser('shopifi');
        $this->logAs('shopifi');
        $catalog = $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'shopifi',
            enabled: true,
            productSelection: [
                [
                    'field' => 'enabled',
                    'operator' => Operator::EQUALS,
                    'value' => true,
                ],
            ],
        );
        $this->createProduct(Uuid::fromString('8d9eef42-10bf-4aff-a5c8-d6c127428045'), [new SetEnabled(true)]); // fixes mutant
        $this->createProduct(Uuid::fromString('00380587-3893-46e6-a8c2-8fee6404cc9e'), [new SetEnabled(false)]);

        $result = $this->query->execute($catalog, '00380587-3893-46e6-a8c2-8fee6404cc9e');

        $this->assertFalse($result);
    }
}
