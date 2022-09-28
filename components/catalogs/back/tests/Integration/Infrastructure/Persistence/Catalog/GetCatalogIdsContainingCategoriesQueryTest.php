<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogIdsContainingCategoriesQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogIdsContainingCategoriesQuery
 */
final class GetCatalogIdsContainingCategoriesQueryTest extends IntegrationTestCase
{
    private ?GetCatalogIdsContainingCategoriesQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(GetCatalogIdsContainingCategoriesQuery::class);
    }

    public function testItGetsCatalogsByCategoryCodes(): void
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

        $this->createCategory([
            'code' => 'tshirt',
            'labels' => ['en_US' => 'T-shirt'],
        ]);
        $this->createCategory([
            'code' => 'hoodie',
            'labels' => ['en_US' => 'Hoodie'],
        ]);
        $this->createCategory([
            'code' => 'shoes',
            'labels' => ['en_US' => 'Shoes'],
        ]);

        $this->setCatalogProductSelection($catalogIdUS, [
            [
                'field' => 'categories',
                'operator' => Operator::IN_LIST,
                'value' => ['tshirt', 'hoodie'],
                'scope' => null,
                'locale' => null,
            ],
        ]);
        $this->setCatalogProductSelection($catalogIdFR, [
            [
                'field' => 'categories',
                'operator' => Operator::IN_LIST,
                'value' => ['tshirt'],
                'scope' => null,
                'locale' => null,
            ],
        ]);

        $resultTshirt = $this->query->execute(['tshirt']);
        $this->assertEquals([$catalogIdUS, $catalogIdFR], $resultTshirt);

        $resultHoodie = $this->query->execute(['hoodie']);
        $this->assertEquals([$catalogIdUS], $resultHoodie);

        $resultShoes = $this->query->execute(['shoes']);
        $this->assertEquals([], $resultShoes);
    }
}
