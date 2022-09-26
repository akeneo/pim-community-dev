<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Domain\Operator;
use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogIdsContainingCategoryQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Catalog\GetCatalogIdsContainingCategoryQuery
 */
class GetCatalogsToDisableOnCategoryRemovalQueryTest extends IntegrationTestCase
{
    private ?GetCatalogIdsContainingCategoryQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(GetCatalogIdsContainingCategoryQuery::class);
    }

    public function testItGetsCatalogsByCategory(): void
    {
        $this->createUser('shopifi');
        $uuidUS = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $uuidFR = 'ed30425c-d9cf-468b-8bc7-fa346f41dd07';
        $uuidUK = '27c53e59-ee6a-4215-a8f1-2fccbb67ba0d';

        $this->createCatalog($uuidUS, 'Store US', 'shopifi');
        $this->createCatalog($uuidFR, 'Store FR', 'shopifi');
        $this->createCatalog($uuidUK, 'Store UK', 'shopifi');

        $this->enableCatalog($uuidUS);
        $this->enableCatalog($uuidFR);
        $this->enableCatalog($uuidUK);

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

        $this->setCatalogProductSelection($uuidUS, [
            [
                'field' => 'category',
                'operator' => Operator::IN_LIST,
                'value' => ['tshirt', 'hoodie'],
                'scope' => null,
                'locale' => null,
            ],
        ]);
        $this->setCatalogProductSelection($uuidFR, [
            [
                'field' => 'category',
                'operator' => Operator::IN_LIST,
                'value' => ['tshirt'],
                'scope' => null,
                'locale' => null,
            ],
        ]);

        $resultTshirt = $this->query->execute('tshirt');
        $this->assertEquals([$uuidUS, $uuidFR], $resultTshirt);

        $resultHoodie = $this->query->execute('hoodie');
        $this->assertEquals([$uuidUS], $resultHoodie);

        $resultShoes = $this->query->execute('shoes');
        $this->assertEquals([], $resultShoes);
    }
}
