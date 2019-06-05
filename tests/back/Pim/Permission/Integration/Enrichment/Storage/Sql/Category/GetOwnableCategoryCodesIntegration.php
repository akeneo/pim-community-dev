<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Enrichment\Storage\Sql\Category;

use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetOwnableCategoryCodes;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use AkeneoTestEnterprise\Pim\Permission\Integration\Enrichment\Storage\ElasticsearchAndSql\CategoryTree\CategoryTreeFixturesLoaderWithPermission;

/**
 * @author    Anaël CHARDAN <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class GetOwnableCategoryCodesIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createAdminUser();

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

        $fixturesLoader->givenTheOwnableCategories([
            'tree_1_child_1_level_3',
            'tree_1_child_1_level_2',
            'tree_1_child_3_level_2'
        ]);
    }

    public function test_it_gets_ownable_categories_for_group_ids(): void
    {
        $expected = ['master', 'tree_1_child_1_level_3', 'tree_1_child_1_level_2', 'tree_1_child_3_level_2'];
        $actual = $this->getQuery()->forGroupIds($this->getAdminUser()->getGroupsIds());

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    private function getAdminUser(): UserInterface
    {
        return $this->testKernel->getContainer()->get('pim_user.repository.user')->findOneByIdentifier('admin');
    }

    private function getQuery(): GetOwnableCategoryCodes
    {
        return $this->testKernel->getContainer()->get('akeneo.pim.enrichment.category.get_ownable_category_codes');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
