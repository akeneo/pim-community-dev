<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogIdsUsingAttributesInProductMappingQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogIdsUsingAttributesInProductMappingQuery
 */
final class GetCatalogIdsUsingAttributesInProductMappingQueryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    /**
     * @dataProvider catalogsUsingAttributesInProductMappingDataProvider
     */
    public function testItGetsCatalogsUsingAttributesInProductMapping(
        array $attributesFirstCatalog,
        array $attributesSecondCatalog,
        array $attributesQueried,
        array $expectedCatalogs,
    ): void {
        $this->createUser('shopifi');

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'shopifi',
            catalogProductMapping: $attributesFirstCatalog,
        );

        $this->createCatalog(
            id: 'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            name: 'Store FR',
            ownerUsername: 'shopifi',
            catalogProductMapping: $attributesSecondCatalog,
        );
        $this->createCatalog(
            id: '27c53e59-ee6a-4215-a8f1-2fccbb67ba0d',
            name: 'Store UK',
            ownerUsername: 'shopifi',
        );

        $resultBothCatalogs = self::getContainer()->get(GetCatalogIdsUsingAttributesInProductMappingQuery::class)->execute($attributesQueried);
        $this->assertEquals($expectedCatalogs, $resultBothCatalogs);
    }

    public function catalogsUsingAttributesInProductMappingDataProvider(): array
    {
        return [
            'gets two catalogs with two attributes' => [
                'attributes_first_catalog' => [
                    'name' => [
                        'scope' => null,
                        'locale' => null,
                        'source' => 'name',
                    ],
                ],
                'attributes_second_catalog' => [
                    'description' => [
                        'scope' => null,
                        'locale' => null,
                        'source' => 'description',
                    ],
                ],
                'attributes_queried' => ['name', 'description'],
                'expected_catalog' => ['db1079b6-f397-4a6a-bae4-8658e64ad47c', 'ed30425c-d9cf-468b-8bc7-fa346f41dd07'],
            ],
            'gets two catalogs with one attribute' => [
                'attributes_first_catalog' => [
                    'name' => [
                        'scope' => null,
                        'locale' => null,
                        'source' => 'name',
                    ],
                    'description' => [
                        'scope' => null,
                        'locale' => null,
                        'source' => 'description',
                    ],
                ],
                'attributes_second_catalog' => [
                    'description' => [
                        'scope' => null,
                        'locale' => null,
                        'source' => 'description',
                    ],
                ],
                'attributes_queried' => ['description'],
                'expected_catalog' => ['db1079b6-f397-4a6a-bae4-8658e64ad47c', 'ed30425c-d9cf-468b-8bc7-fa346f41dd07'],
            ],
            'gets only one catalog with one attribute' => [
                'attributes_first_catalog' => [
                    'name' => [
                        'scope' => null,
                        'locale' => null,
                        'source' => 'name',
                    ],
                    'description' => [
                        'scope' => null,
                        'locale' => null,
                        'source' => 'description',
                    ],
                ],
                'attributes_second_catalog' => [
                    'description' => [
                        'scope' => null,
                        'locale' => null,
                        'source' => 'description',
                    ],
                ],
                'attributes_queried' => ['name'],
                'expected_catalog' => ['db1079b6-f397-4a6a-bae4-8658e64ad47c'],
            ],
            'gets no catalogs with one attribute' => [
                'attributes_first_catalog' => ['name', 'description'],
                'attributes_second_catalog' => ['description'],
                'attributes_queried' => ['erp_name'],
                'expected_catalog' => [],
            ],
        ];
    }
}
