<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Enrichment\Storage\Sql\Category;

use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetRootCategoryCodesAndLabels;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use AkeneoTestEnterprise\Pim\Permission\Integration\Enrichment\Storage\ElasticsearchAndSql\CategoryTree\CategoryTreeFixturesLoaderWithPermission;

class GetRootCategoryCodesAndLabelsIntegration extends TestCase
{
    private CategoryTreeFixturesLoaderWithPermission $fixturesLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAdminUser();

        $this->fixturesLoader = $this->get('akeneo_integration_tests.loader.category_tree_loader_with_permissions');

        $this->fixturesLoader->adminUserAsRedactorAndITSupport();

        $this->fixturesLoader->givenTheCategoryTreesWithoutViewPermission([
            'tree_1' => [
                'tree_1_child_1_level_1' => [
                    'tree_1_child_1_level_2' => [],
                ],
                'tree_1_child_2_level_1' => [],
            ],
            'tree_2' => [
                'tree_2_child_1_level_1' => [],
            ],
            'tree_3' => [],
            'tree_4' => [],
        ]);
        $this->removeLabelFromCategory('tree_3');
    }

    public function test_it_gets_paginated_root_categories(): void
    {
        $expectedFirstPage = [
            [
                'code' => 'master',
                'label' => 'Master catalog',
            ],
            [
                'code' => 'tree_1',
                'label' => 'Tree_1',
            ],
        ];
        $firstPage = $this->getQuery()->execute(
            $this->getAdminUser()->getUiLocale()->getCode(),
            '',
            0,
            2
        );

        $this->assertEqualsCanonicalizing($expectedFirstPage, $firstPage);

        $expectedSecondPage = [
            [
                'code' => 'tree_2',
                'label' => 'Tree_2',
            ],
            [
                'code' => 'tree_3',
                'label' => null,
            ],
        ];
        $secondPage = $this->getQuery()->execute(
            $this->getAdminUser()->getUiLocale()->getCode(),
            '',
            2,
            2
        );

        $this->assertEqualsCanonicalizing($expectedSecondPage, $secondPage);
    }

    public function test_it_filter_root_categories(): void
    {
        $search = 'Tree_2';
        $expectedResults = [
            [
                'code' => 'tree_2',
                'label' => 'Tree_2',
            ],
        ];
        $results = $this->getQuery()->execute(
            $this->getAdminUser()->getUiLocale()->getCode(),
            $search,
            0,
            10
        );

        $this->assertEqualsCanonicalizing($expectedResults, $results);
    }

    private function removeLabelFromCategory(string $label): void
    {
        $query = <<<SQL
UPDATE pim_catalog_category_translation
SET label = null
WHERE label = :label;
SQL;

        $this->get('database_connection')->executeQuery($query, ['label' => $label]);
    }

    private function getAdminUser(): UserInterface
    {
        return $this->get('pim_user.repository.user')->findOneByIdentifier('admin');
    }

    private function getQuery(): GetRootCategoryCodesAndLabels
    {
        return $this->get(GetRootCategoryCodesAndLabels::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
