<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Category;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Category\Sql\SqlCountTotalCategoriesPerTree;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Category\CategoryTree;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

final class SqlCountTotalCategoriesPerTreeIntegration extends TestCase
{
    public SqlCountTotalCategoriesPerTree $countTotalCategoriesPerTree;

    public function setUp(): void
    {
        parent::setUp();
        $this->countTotalCategoriesPerTree = $this->get('akeneo.pim.structure.query.count_total_categories_per_tree');

        /**
         * master
         * |_____ furniture
         *        |_________ desk
         *        |_________ library
         * |_____ clothes
         *        |_________ shoes
         *        |_________ tshirt
         *
         * season
         * |_____ winter
         * |_____ summer
         */
        $this->givenCategories(
            [
                [
                    'code' => 'furniture',
                    'parent' => 'master'
                ],
                [
                    'code' => 'desk',
                    'parent' => 'furniture'
                ],
                [
                    'code' => 'library',
                    'parent' => 'furniture'
                ],
                [
                    'code' => 'clothes',
                    'parent' => 'master'
                ],
                [
                    'code' => 'shoes',
                    'parent' => 'clothes'
                ],
                [
                    'code' => 'tshirt',
                    'parent' => 'clothes'
                ],
                [
                    'code' => 'season',
                    'parent' => null
                ],
                [
                    'code' => 'winter',
                    'parent' => 'season'
                ],
                [
                    'code' => 'summer',
                    'parent' => 'season'
                ],
            ]
        );
    }

    /**
     * @test
     * @dataProvider invalidCategoryCodes
     */
    public function it_throws_if_the_category_codes_are_not_non_empty_strings($invalidCategoryCode)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->countTotalCategoriesPerTree->execute([$invalidCategoryCode], true);
    }

    public function invalidCategoryCodes(): array
    {
        return [
            'Category code cannot be empty' => [''],
            'Category code cannot be an integer' => [''],
            'Category code cannot be an object' => [new \stdClass()],
        ];
    }

    /**
     * @test
     * @dataProvider categoryCodesToSelectWithoutChildren
     */
    public function it_counts_the_total_selected_categories_without_counting_children_for_each_category_tree($categoryCodes, $expected): void
    {
        $actual = $this->countTotalCategoriesPerTree->execute($categoryCodes, false);

        $this->assertEquals($expected, $actual);
    }

    public function categoryCodesToSelectWithoutChildren(): array
    {
        return [
            'Select No categories' => [[], ['master' => 0, 'season' => 0]],
            'Select root category' => [['master'], ['master' => 1, 'season' => 0]],
            'Selection in 2 trees' => [
                ['desk', 'clothes', 'winter'],
                [
                    'master' => 2, // desk = 1 + clothes = 1 + 2 children = 3
                    'season' => 1      // winter = 1
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider categoryCodesToSelectWithChildren
     */
    public function it_counts_the_total_selected_categories_with_children_for_each_category_tree($categoryCodes, $expectedResults): void
    {
        $actual = $this->countTotalCategoriesPerTree->execute($categoryCodes, true);
        $this->assertEquals($expectedResults, $actual);
    }

    public function categoryCodesToSelectWithChildren(): array
    {
        return [
            'Select No categories' => [[], ['master' => 0, 'season' => 0]],
            'Select root category' => [['master'], ['master' => 7, 'season' => 0]],
            'Selection in 2 trees' => [
                ['desk', 'clothes', 'winter'],
                [
                    'master' => 1 + 3, // desk = 1 + clothes = 1 + 2 children = 3
                    'season' => 1      // winter = 1
                ],
            ],
        ];
    }

    private function givenCategories(array $categories): void
    {
        $categories = array_walk(
            $categories,
            function (array $categoryData) {
                $category = $this->get('pim_catalog.factory.category')->create();
                $this->get('pim_catalog.updater.category')->update($category, $categoryData);
                $constraintViolations = $this->get('validator')->validate($category);

                Assert::count($constraintViolations, 0);
                $this->get('pim_catalog.saver.category')->save($category);

                return $category;
            }
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
