<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogIdsUsingLocalesAsFilterQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogIdsUsingLocalesAsFilterQuery
 */
final class GetCatalogIdsUsingLocalesAsFilterQueryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    /**
     * @dataProvider catalogsByLocalesDataProvider
     */
    public function testItGetsCatalogsByLocale(
        array $localesFirstCatalog,
        array $localesSecondCatalog,
        array $localesQueried,
        array $expectedCatalogs,
    ): void {
        $this->createUser('shopifi');

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'shopifi',
            catalogProductValueFilters: ['locales' => $localesFirstCatalog],
        );
        $this->createCatalog(
            id: 'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            name: 'Store FR',
            ownerUsername: 'shopifi',
            catalogProductValueFilters: ['locales' => $localesSecondCatalog],
        );
        $this->createCatalog(
            id: '27c53e59-ee6a-4215-a8f1-2fccbb67ba0d',
            name: 'Store UK',
            ownerUsername: 'shopifi',
        );

        $resultBothCatalogs = self::getContainer()->get(GetCatalogIdsUsingLocalesAsFilterQuery::class)->execute($localesQueried);
        $this->assertEquals($expectedCatalogs, $resultBothCatalogs);
    }

    public function catalogsByLocalesDataProvider(): array
    {
        return [
            'gets two catalogs with two locales' => [
                'locales_first_catalog' => ['en_US'],
                'locales_second_catalog' => ['fr_FR'],
                'locales_queried' => ['en_US', 'fr_FR'],
                'expected_catalog' => ['db1079b6-f397-4a6a-bae4-8658e64ad47c', 'ed30425c-d9cf-468b-8bc7-fa346f41dd07'],
            ],
            'gets two catalogs with one locale' => [
                'locales_first_catalog' => ['en_US', 'fr_FR'],
                'locales_second_catalog' => ['fr_FR'],
                'locales_queried' => ['fr_FR'],
                'expected_catalog' => ['db1079b6-f397-4a6a-bae4-8658e64ad47c', 'ed30425c-d9cf-468b-8bc7-fa346f41dd07'],
            ],
            'gets only one catalog with one locale' => [
                'locales_first_catalog' => ['en_US', 'fr_FR'],
                'locales_second_catalog' => ['fr_FR'],
                'locales_queried' => ['en_US'],
                'expected_catalog' => ['db1079b6-f397-4a6a-bae4-8658e64ad47c'],
            ],
            'gets no catalogs with one locale' => [
                'locales_first_catalog' => ['en_US', 'fr_FR'],
                'locales_second_catalog' => ['fr_FR'],
                'locales_queried' => ['GBP'],
                'expected_catalog' => [],
            ],
        ];
    }
}
