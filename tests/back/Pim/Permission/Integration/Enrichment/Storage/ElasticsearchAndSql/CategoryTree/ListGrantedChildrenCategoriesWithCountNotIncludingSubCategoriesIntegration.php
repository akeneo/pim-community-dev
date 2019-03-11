<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Enrichment\Storage\ElasticsearchAndSql\CategoryTree;

use Akeneo\Pim\Enrichment\Component\Category\CategoryTree\ReadModel\ChildCategory;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class ListGrantedChildrenCategoriesWithCountNotIncludingSubCategoriesIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $fixturesLoader = new CategoryTreeFixturesLoaderWithPermission($this->testKernel->getContainer());
        $fixturesLoader->adminUserAsRedactorAndITSupport();
        $fixturesLoader->givenTheCategoryTreesWithoutViewPermission([
            'tree_1' => [
                'tree_1_child_1_level_1' => [
                    'tree_1_child_1_level_2' => [
                        'tree_1_child_1_level_3' => []
                    ],
                    'tree_1_child_2_level_2' => [],
                ],
                'tree_1_child_2_level_1' => [
                    'tree_1_child_3_level_2' => []
                ],
                'tree_1_child_3_level_1' => [],
            ],
            'tree_2' => [
                'tree_2_child_1_level_1' => [
                    'tree_2_child_1_level_2' => []
                ]
            ]
        ]);

        $fixturesLoader->givenTheViewableCategories([
            'tree_1',
            'tree_1_child_1_level_1',
            'tree_1_child_1_level_2',
            'tree_1_child_2_level_1',
            'tree_2',
            'tree_2_child_1_level_1'
        ]);

        $fixturesLoader->givenTheProductsWithCategories([
            'product_1' => ['tree_1', 'tree_1_child_1_level_1'],
            'product_2' => ['tree_1_child_1_level_1'],
            'product_3' => ['tree_1_child_1_level_3'],
            'product_4' => ['tree_1_child_2_level_2'],
            'product_5' => ['tree_1_child_2_level_1', 'tree_2_child_1_level_2'],
        ]);
    }

    public function test_list_child_categories_with_permissions_applied()
    {
        $query = $this->get('akeneo.pim.enrichment.category.category_tree.query.list_children_categories_with_count_not_including_sub_categories');
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier('admin');
        $fromCategory = $this->get('pim_catalog.repository.category')->findOneByIdentifier('tree_1');

        $result = $query->list('en_US', $user->getId(), $fromCategory->getId(), null);

        $expectedCategories = [
            new ChildCategory(1, 'tree_1_child_1_level_1', 'Tree_1_child_1_level_1', false, false, 2, []),
            new ChildCategory(2, 'tree_1_child_2_level_1', 'Tree_1_child_2_level_1', false, true, 1, []),
        ];

        $this->assertSameListOfChildCategories($expectedCategories, $result);
    }

    public function test_list_child_categories_with_a_category_selected_as_filter_with_permissions_applied()
    {
        $query = $this->get('akeneo.pim.enrichment.category.category_tree.query.list_children_categories_with_count_not_including_sub_categories');
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier('admin');
        $fromCategory = $this->get('pim_catalog.repository.category')->findOneByIdentifier('tree_1');
        $toCategory = $this->get('pim_catalog.repository.category')->findOneByIdentifier('tree_1_child_1_level_2');

        $result = $query->list('en_US', $user->getId(), $fromCategory->getId(), $toCategory->getId());

        $expectedCategories = [
            new ChildCategory(1, 'tree_1_child_1_level_1', 'Tree_1_child_1_level_1', false, false, 2, [
                new ChildCategory(2, 'tree_1_child_1_level_2', 'Tree_1_child_1_level_2', true, true, 0, []),
            ]),
            new ChildCategory(2, 'tree_1_child_2_level_1', 'Tree_1_child_2_level_1', false, true, 1, []),
        ];

        $this->assertSameListOfChildCategories($expectedCategories, $result);
    }

    /**
     * @param ChildCategory[] $expectedCategories
     * @param ChildCategory[] $categories
     */
    private function assertSameListOfChildCategories(array $expectedCategories, array $categories): void
    {
        $i = 0;
        foreach ($expectedCategories as $expectedCategory) {
            $this->assertSameChildCategory($expectedCategory, $categories[$i]);
            $i++;
        }
    }

    /**
     * @param ChildCategory $expectedCategory
     * @param ChildCategory $category
     */
    private function assertSameChildCategory(ChildCategory $expectedCategory, ChildCategory $category): void
    {
        Assert::assertEquals($expectedCategory->code(), $category->code());
        Assert::assertEquals($expectedCategory->isLeaf(), $category->isLeaf());
        Assert::assertEquals($expectedCategory->expanded(), $category->expanded());
        Assert::assertEquals($expectedCategory->label(), $category->label());
        Assert::assertEquals($expectedCategory->selectedAsFilter(), $category->selectedAsFilter());
        Assert::assertEquals($expectedCategory->numberProductsInCategory(), $category->numberProductsInCategory());
        $this->assertSameListOfChildCategories($expectedCategory->childrenCategoriesToExpand(), $category->childrenCategoriesToExpand());
    }

    /**
     * @inheritDoc
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}

