<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Category;

use Akeneo\Catalogs\Infrastructure\Persistence\Category\GetCategoriesByCodeQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Category\GetCategoriesByCodeQuery
 */
class GetCategoriesByCodeQueryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsCategoriesFromCodeList(): void
    {
        $this->createCategory(['code' => 'tshirt', 'labels' => ['en_US' => 'T-shirt']]);
        $this->createCategory(['code' => 'shoes', 'labels' => ['fr_FR' => 'Chaussures', 'en_US' => 'Shoes']]);
        $this->createCategory(['code' => 'pants', 'labels' => []]);
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
            'label' => '[pants]',
            'isLeaf' => false,
        ];

        $result = self::getContainer()->get(GetCategoriesByCodeQuery::class)->execute(['tshirt', 'shoes', 'pants', 'non_existing_category'], 'en_US');

        $this->assertEquals([
            $expectedPantsCategory,
            $expectedShoesCategory,
            $expectedTshirtCategory,
        ], $result);
    }

    public function testItReturnsAnEmptyArrayForAnEmptyCodeList(): void
    {
        $this->createCategory(['code' => 'tshirt']);

        $result = self::getContainer()->get(GetCategoriesByCodeQuery::class)->execute([], 'en_US');

        $this->assertEmpty($result, 'No category should be found');
    }

    public function testItReturnsAnEmptyArrayForInvalidCodeList(): void
    {
        $this->createCategory(['code' => 'tshirt']);

        $result = self::getContainer()->get(GetCategoriesByCodeQuery::class)->execute(['unknown', 'shoes', 'pants'], 'en_US');

        $this->assertEmpty($result, 'No category should be found');
    }
}
