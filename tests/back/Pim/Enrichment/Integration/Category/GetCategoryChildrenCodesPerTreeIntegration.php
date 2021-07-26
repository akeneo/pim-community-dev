<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Category;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Category\SqlGetCategoryChildrenCodesPerTree;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

final class GetCategoryChildrenCodesPerTreeIntegration extends TestCase
{
    public SqlGetCategoryChildrenCodesPerTree $getCategoryChildrenCodesPerTree;

    public function setUp(): void
    {
        parent::setUp();
        $this->getCategoryChildrenCodesPerTree = $this->get('akeneo.enrichment.public_api.get_category_children_codes_per_tree');

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
        $this->getCategoryChildrenCodesPerTree->executeWithoutChildren([$invalidCategoryCode]);
    }

    /**
     * @test
     * @dataProvider invalidCategoryCodes
     */
    public function it_throws_if_the_category_codes_are_not_non_empty_strings_with_children($invalidCategoryCode)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->getCategoryChildrenCodesPerTree->executeWithChildren([$invalidCategoryCode]);
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
        $actual = $this->getCategoryChildrenCodesPerTree->executeWithoutChildren($categoryCodes);

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
        $actual = $this->getCategoryChildrenCodesPerTree->executeWithChildren($categoryCodes);
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

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
