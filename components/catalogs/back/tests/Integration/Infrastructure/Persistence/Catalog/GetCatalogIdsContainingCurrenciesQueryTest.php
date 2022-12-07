<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Domain\Operator;
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

    public function testItGetsCatalogsByCurrency(): void
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

        $this->setCatalogProductSelection($catalogIdUS, [
            [
                'field' => 'currencies',
                'operator' => Operator::IN_LIST,
                'value' => ['USD', 'EUR'],
                'scope' => null,
                'locale' => null,
            ],
        ]);
        $this->setCatalogProductSelection($catalogIdFR, [
            [
                'field' => 'currencies',
                'operator' => Operator::IN_LIST,
                'value' => ['EUR'],
                'scope' => null,
                'locale' => null,
            ],
        ]);

        $resultBothCatalogs = $this->query->execute(['EUR']);
        $this->assertEquals([$catalogIdUS, $catalogIdFR], $resultBothCatalogs);

        $resultUSDCatalog = $this->query->execute(['USD']);
        $this->assertEquals([$catalogIdUS], $resultUSDCatalog);

        $resultNoCatalog = $this->query->execute(['GBP']);
        $this->assertEquals([], $resultNoCatalog);
    }
}
