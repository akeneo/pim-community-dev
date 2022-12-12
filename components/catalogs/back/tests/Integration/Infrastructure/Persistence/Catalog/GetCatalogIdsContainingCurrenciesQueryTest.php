<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogIdsContainingCurrenciesQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogIdsContainingCurrenciesQuery
 */
final class GetCatalogIdsContainingCurrenciesQueryTest extends IntegrationTestCase
{
    private ?GetCatalogIdsContainingCurrenciesQuery $query;
    private ?Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->connection = self::getContainer()->get(Connection::class);
        $this->query = self::getContainer()->get(GetCatalogIdsContainingCurrenciesQuery::class);
    }

    /**
     * @dataProvider catalogsByCurrenciesDataProvider
     */
    public function testItGetsCatalogsByCurrency(
        array $currenciesFirstCatalog,
        array $currenciesSecondCatalog,
        array $currenciesQueried,
        array $expectedCatalogs,
    ): void
    {
        $this->createUser('shopifi');
        $catalogIdUS = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $catalogIdFR = 'ed30425c-d9cf-468b-8bc7-fa346f41dd07';
        $catalogIdUK = '27c53e59-ee6a-4215-a8f1-2fccbb67ba0d';

        $this->createCatalog($catalogIdUS, 'Store US', 'shopifi');
        $this->createCatalog($catalogIdFR, 'Store FR', 'shopifi');
        $this->createCatalog($catalogIdUK, 'Store UK', 'shopifi');

        $this->enableCatalog($catalogIdUS);
        $this->enableCatalog($catalogIdFR);
        $this->enableCatalog($catalogIdUK);

        $this->setCatalogProductValueFilters($catalogIdUS, [
            'currencies' => $currenciesFirstCatalog,
        ]);
        $this->setCatalogProductValueFilters($catalogIdFR, [
            'currencies' => $currenciesSecondCatalog,
        ]);

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
                'expected_catalog' => ['db1079b6-f397-4a6a-bae4-8658e64ad47c', 'ed30425c-d9cf-468b-8bc7-fa346f41dd07']
            ],
            'gets two catalogs with one currency' => [
                'currencies_first_catalog' => ['USD', 'EUR'],
                'currencies_second_catalog' => ['EUR'],
                'currencies_queried' => ['EUR'],
                'expected_catalog' => ['db1079b6-f397-4a6a-bae4-8658e64ad47c', 'ed30425c-d9cf-468b-8bc7-fa346f41dd07']
            ],
            'gets only one catalog with one currency' => [
                'currencies_first_catalog' => ['USD', 'EUR'],
                'currencies_second_catalog' => ['EUR'],
                'currencies_queried' => ['USD'],
                'expected_catalog' => ['db1079b6-f397-4a6a-bae4-8658e64ad47c']
            ],
            'gets no catalogs with one currency' => [
                'currencies_first_catalog' => ['USD', 'EUR'],
                'currencies_second_catalog' => ['EUR'],
                'currencies_queried' => ['GBP'],
                'expected_catalog' => []
            ],
        ];
    }
}
