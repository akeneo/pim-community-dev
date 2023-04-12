<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Category;

use Akeneo\Catalogs\Infrastructure\Persistence\Category\GetCategoryChildrenQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Category\GetCategoryChildrenQuery
 */
class GetCategoryChildrenQueryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItGetsCategoryChildren(): void
    {
        $this->createCategory(['code' => 'parent_category']);
        $this->createCategory([
            'code' => 'child1',
            'parent' => 'parent_category',
            'labels' => ['en_US' => 'Child category'],
        ]);

        $this->createCategory([
            'code' => 'child2',
            'parent' => 'parent_category',
            'labels' => ['en_US' => 'Child 2 category', 'fr_FR' => 'Categorie enfant 2'],
        ]);

        $this->createCategory([
            'code' => 'child3',
            'parent' => 'parent_category',
            'labels' => [],
        ]);

        $this->createCategory(['code' => 'grand_child', 'parent' => 'child1']);

        $expectedChild1 = [
            'code' => 'child1',
            'label' => 'Child category',
            'isLeaf' => false,
        ];

        $expectedChild2 = [
            'code' => 'child2',
            'label' => 'Child 2 category',
            'isLeaf' => true,
        ];

        $expectedChild3 = [
            'code' => 'child3',
            'label' => '[child3]',
            'isLeaf' => true,
        ];

        $result = self::getContainer()->get(GetCategoryChildrenQuery::class)->execute('parent_category', 'en_US');

        $this->assertEquals([$expectedChild1, $expectedChild2, $expectedChild3], $result);
    }

    public function testItReturnsAnEmptyArrayWithUnknownCategoryCode(): void
    {
        $this->createCategory(['code' => 'parent_category']);

        $result = self::getContainer()->get(GetCategoryChildrenQuery::class)->execute('some_category_code', 'en_US');

        $this->assertEmpty($result, 'Unknown category code should not have any children');
    }
}
