<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogIdsUsingCurrenciesAsFilterQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogIdsUsingCurrenciesAsFilterQuery
 */
final class GetCatalogIdsUsingCurrenciesAsFilterQueryTest extends IntegrationTestCase
{
    private ?GetCatalogIdsUsingCurrenciesAsFilterQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(GetCatalogIdsUsingCurrenciesAsFilterQuery::class);
    }

    /**
     * @dataProvider catalogsByCurrenciesDataProvider
     */
    public function testItGetsCatalogsByCurrency(
        array $currenciesFirstCatalog,
        array $currenciesSecondCatalog,
        array $currenciesQueried,
        array $expectedCatalogs,
    ): void {
        $this->createUser('shopifi');

        $this->createCatalog(
            id: 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            name: 'Store US',
            ownerUsername: 'shopifi',
            catalogProductValueFilters: ['currencies' => $currenciesFirstCatalog],
        );
        $this->createCatalog(
            id: 'ed30425c-d9cf-468b-8bc7-fa346f41dd07',
            name: 'Store FR',
            ownerUsername: 'shopifi',
            catalogProductValueFilters: ['currencies' => $currenciesSecondCatalog],
        );
        $this->createCatalog(
            id: '27c53e59-ee6a-4215-a8f1-2fccbb67ba0d',
            name: 'Store UK',
            ownerUsername: 'shopifi',
        );

        $resultBothCatalogs = $this->query->execute($currenciesQueried);
        $this->assertEquals($expectedCatalogs, $resultBothCatalogs);
    }

    public function catalogsByCurrenciesDataProvider(): array
    {
        return [
            'gets two catalogs with two currencies' => [
                'currencies_first_catalog' => ['USD'],
                'currencies_second_catalog' => ['EUR'],
                'currencies_queried' => ['USD', 'EUR'],
                'expected_catalog' => ['db1079b6-f397-4a6a-bae4-8658e64ad47c', 'ed30425c-d9cf-468b-8bc7-fa346f41dd07'],
            ],
            'gets two catalogs with one currency' => [
                'currencies_first_catalog' => ['USD', 'EUR'],
                'currencies_second_catalog' => ['EUR'],
                'currencies_queried' => ['EUR'],
                'expected_catalog' => ['db1079b6-f397-4a6a-bae4-8658e64ad47c', 'ed30425c-d9cf-468b-8bc7-fa346f41dd07'],
            ],
            'gets only one catalog with one currency' => [
                'currencies_first_catalog' => ['USD', 'EUR'],
                'currencies_second_catalog' => ['EUR'],
                'currencies_queried' => ['USD'],
                'expected_catalog' => ['db1079b6-f397-4a6a-bae4-8658e64ad47c'],
            ],
            'gets no catalogs with one currency' => [
                'currencies_first_catalog' => ['USD', 'EUR'],
                'currencies_second_catalog' => ['EUR'],
                'currencies_queried' => ['GBP'],
                'expected_catalog' => [],
            ],
        ];
    }
}
