<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Infrastructure\Persistence\GetCategoriesByCodeQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\GetCategoriesByCodeQuery
 */
class GetCategoriesByCodeQueryTest extends IntegrationTestCase
{
    private ?GetCategoriesByCodeQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(GetCategoriesByCodeQuery::class);
    }

    public function testItGetsCategoriesFromCodeList(): void
    {
        $this->createCategory(['code' => 'tshirt', 'labels' => ['en_US' => 'T-shirt']]);
        $this->createCategory(['code' => 'shoes', 'labels' => ['en_US' => 'Shoes']]);
        $this->createCategory(['code' => 'pants', 'labels' => ['en_US' => 'Pants']]);
        $this->createCategory(['code' => 'shorts', 'parent' => 'pants']);

        $expectedTshirtCategory = [
            'code' => 'tshirt',
            'label' => 'T-shirt',
            'isLeaf' => true,
        ];

        $expectedShoesCategory = [
            'code' => 'shoes',
            'label' => 'Shoes',
            'isLeaf' => true,
        ];

        $expectedPantsCategory = [
            'code' => 'pants',
            'label' => 'Pants',
            'isLeaf' => false,
        ];

        $result = $this->query->execute(['tshirt', 'shoes', 'pants', 'non_existing_category']);

        $this->assertEquals([
            $expectedPantsCategory,
            $expectedShoesCategory,
            $expectedTshirtCategory,
        ], $result);
    }

    public function testItReturnsAnEmptyArrayForAnEmptyCodeList(): void
    {
        $this->createCategory(['code' => 'tshirt']);

        $result = $this->query->execute([]);

        $this->assertEmpty($result, 'No category should be found');
    }

    public function testItReturnsAnEmptyArrayForInvalidCodeList(): void
    {
        $this->createCategory(['code' => 'tshirt']);

        $result = $this->query->execute(['unknown', 'shoes', 'pants']);

        $this->assertEmpty($result, 'No category should be found');
    }
}
