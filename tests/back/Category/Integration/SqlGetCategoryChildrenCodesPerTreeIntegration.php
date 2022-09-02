<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Integration;

use Akeneo\Pim\Enrichment\Bundle\Filter\CategoryCodeFilterInterface;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Category\SqlGetCategoryChildrenCodesPerTree;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

final class SqlGetCategoryChildrenCodesPerTreeIntegration extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

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
        $this->getQuery(new AllowAllCategoryCode())->executeWithoutChildren([$invalidCategoryCode]);
    }

    /**
     * @test
     * @dataProvider invalidCategoryCodes
     */
    public function it_throws_if_the_category_codes_are_not_non_empty_strings_with_children($invalidCategoryCode)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->getQuery(new AllowAllCategoryCode())->executeWithChildren([$invalidCategoryCode]);
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
    public function it_returns_the_existing_categories_code_for_each_category_tree($categoryCodes, $expected): void
    {
        $actual = $this->getQuery(new AllowAllCategoryCode())->executeWithoutChildren($categoryCodes);

        self::assertEqualsCanonicalizing($expected, $actual);
    }

    public function categoryCodesToSelectWithoutChildren(): array
    {
        return [
            'Select No categories' => [[], ['master' => [], 'season' => []]],
            'Select root category' => [['master'], ['master' => ['master'], 'season' => []]],
            'Selection in 2 trees' => [
                ['desk', 'clothes', 'winter'],
                [
                    'master' => ['desk', 'clothes'],
                    'season' => ['winter']
                ],
            ],
            'Select nonexistent category code' => [['nonexistent_category_code'], ['master' => [], 'season' => []]],
        ];
    }

    /**
     * @test
     * @dataProvider categoryCodesToSelectWithChildren
     */
    public function it_return_children_code_of_the_given_category_codes_for_each_category_tree($categoryCodes, $expectedResults): void
    {
        $actual = $this->getQuery(new AllowAllCategoryCode())->executeWithChildren($categoryCodes);
        self::assertEqualsCanonicalizing($expectedResults, $actual);
    }

    public function categoryCodesToSelectWithChildren(): array
    {
        return [
            'Select No categories' => [[], ['master' => [], 'season' => []]],
            'Select root category' => [
                ['master'],
                [
                    'master' => ['master', 'furniture', 'desk', 'library', 'clothes', 'shoes', 'tshirt'],
                    'season' => []
                ]
            ],
            'Selection in 2 trees' => [
                ['desk', 'clothes', 'winter'],
                [
                    'master' => ['clothes', 'shoes', 'tshirt', 'desk'],
                    'season' => ['winter']
                ],
            ],
        ];
    }

    /**
     * @test
     */
    public function it_filter_the_categories_when_searching_without_children()
    {
        $actual = $this->getQuery(new DenyAllCategoryCode())->executeWithoutChildren(['master']);
        self::assertEqualsCanonicalizing(['master' => [], 'season' => []], $actual);
    }

    /**
     * @test
     */
    public function it_filter_the_categories_when_searching_with_children()
    {
        $actual = $this->getQuery(new DenyAllCategoryCode())->executeWithChildren(['master']);
        self::assertEqualsCanonicalizing(['master' => [], 'season' => []], $actual);
    }

    private function givenCategories(array $categories): void
    {
        foreach ($categories as $categoryData) {
            $category = $this->get('pim_catalog.factory.category')->create();
            $this->get('pim_catalog.updater.category')->update($category, $categoryData);
            $constraintViolations = $this->get('validator')->validate($category);

            Assert::count($constraintViolations, 0);
            $this->get('pim_catalog.saver.category')->save($category);
        }
    }

    private function getQuery(CategoryCodeFilterInterface $categoryCodeFilter): SqlGetCategoryChildrenCodesPerTree
    {
        return new SqlGetCategoryChildrenCodesPerTree(
            $this->get('database_connection'),
            $categoryCodeFilter
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}

class AllowAllCategoryCode implements CategoryCodeFilterInterface
{
    public function filter(array $codes): array
    {
        return $codes;
    }
}

class DenyAllCategoryCode implements CategoryCodeFilterInterface
{
    public function filter(array $codes): array
    {
        return [];
    }
}
