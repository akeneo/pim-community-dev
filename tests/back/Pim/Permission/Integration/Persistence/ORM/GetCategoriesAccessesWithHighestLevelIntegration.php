<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Persistence\ORM;

use Akeneo\Pim\Permission\Bundle\Persistence\ORM\Category\GetCategoriesAccessesWithHighestLevel;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use AkeneoTestEnterprise\Pim\Permission\Integration\Enrichment\Storage\ElasticsearchAndSql\CategoryTree\CategoryTreeFixturesLoaderWithPermission;

class GetCategoriesAccessesWithHighestLevelIntegration extends TestCase
{
    private GetCategoriesAccessesWithHighestLevel $query;
    private CategoryTreeFixturesLoaderWithPermission $fixturesLoader;
    private int $groupId;

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(GetCategoriesAccessesWithHighestLevel::class);

        $this->createAdminUser();

        $this->fixturesLoader = $this->get('akeneo_integration_tests.loader.category_tree_loader_with_permissions');

        $this->fixturesLoader->adminUserAsRedactorAndITSupport();

        $this->groupId = $this->get('pim_user.repository.group')->findOneByIdentifier('redactor')->getId();

        $this->fixturesLoader->givenTheCategoryTreesWithoutViewPermission([
            'a_tree' => [
                'a_tree_child_A' => [],
                'a_tree_child_B' => [],
                'a_tree_child_C' => [],
            ],
            'b_tree' => [
                'b_tree_child_A' => [],
            ]
        ]);
    }

    public function categoryHighestAccessLevelDataProvider(): array
    {
        return [
            'test master category is owned without additional permissions' => [
                'expected' => [
                    'master' => Attributes::OWN_PRODUCTS,
                ],
                'viewableCategories' => [],
                'editableCategories' => [],
                'ownableCategories' => [],
            ],
            'test it returns highest permissions level for each category' => [
                'expected' => [
                    'master' => Attributes::OWN_PRODUCTS,
                    'a_tree' => Attributes::VIEW_ITEMS,
                    'a_tree_child_A' => Attributes::EDIT_ITEMS,
                    'a_tree_child_B' => Attributes::EDIT_ITEMS,
                    'b_tree' => Attributes::OWN_PRODUCTS,
                    'b_tree_child_A' => Attributes::OWN_PRODUCTS,
                ],
                'viewableCategories' => [
                    'a_tree',
                    'a_tree_child_A',
                    'a_tree_child_B',
                    'b_tree',
                    'b_tree_child_A'
                ],
                'editableCategories' => [
                    'a_tree_child_A',
                    'a_tree_child_B',
                    'b_tree',
                    'b_tree_child_A'
                ],
                'ownableCategories' => [
                    'b_tree',
                    'b_tree_child_A',
                ],
            ],
        ];
    }

    /**
     * @dataProvider categoryHighestAccessLevelDataProvider
     */
    public function testItFetchesCategoriesHighestAccessLevel(
        array $expected,
        array $viewableCategories,
        array $editableCategories,
        array $ownableCategories
    ): void {
        $this->fixturesLoader->givenTheViewableCategories($viewableCategories);
        $this->fixturesLoader->givenTheEditableCategories($editableCategories);
        $this->fixturesLoader->givenTheOwnableCategories($ownableCategories);

        $results = $this->query->execute($this->groupId);

        $this->assertSame($expected, $results);
    }
}

