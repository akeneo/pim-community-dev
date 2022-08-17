<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Infrastructure\Persistence\GetCategoryChildrenQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\GetCategoryChildrenQuery
 */
class GetCategoryChildrenQueryTest extends IntegrationTestCase
{
    private ?GetCategoryChildrenQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->query = self::getContainer()->get(GetCategoryChildrenQuery::class);
    }

    public function testItGetsCategoryChildren(): void
    {
        $this->createCategory(['code' => 'parent_category']);
        $this->createCategory([
            'code' => 'child1',
            'parent' => 'parent_category',
            'labels' => ['en_US' => 'Child category']
        ]);

        $this->createCategory([
            'code' => 'child2',
            'parent' => 'parent_category',
            'labels' => ['en_US' => 'Child 2 category']
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

        $result = $this->query->execute('parent_category');

        $this->assertEquals([$expectedChild1, $expectedChild2], $result);
    }

    public function testItReturnsAnEmptyArrayWithUnknownCategoryCode(): void
    {
        $this->createCategory(['code' => 'parent_category']);

        $result = $this->query->execute('some_category_code');

        $this->assertEmpty($result, 'Unknown category code should not have any children');
    }
}
