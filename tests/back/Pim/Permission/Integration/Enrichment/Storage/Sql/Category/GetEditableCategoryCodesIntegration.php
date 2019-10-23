<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Enrichment\Storage\Sql\Category;

use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetEditableCategoryCodes;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;

/**
 * @author    Anaël CHARDAN <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class GetEditableCategoryCodesIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createAdminUser();

        $fixturesLoader = $this->get('akeneo_integration_tests.loader.category_tree_loader_with_permissions');

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

        $fixturesLoader->givenTheEditableCategories([
            'tree_1_child_1_level_3',
            'tree_1_child_1_level_2',
            'tree_1_child_3_level_2'
        ]);
    }

    public function test_it_gets_editable_categories_for_group_ids(): void
    {
        $expected = ['master', 'tree_1_child_1_level_3', 'tree_1_child_1_level_2', 'tree_1_child_3_level_2'];
        $actual = $this->getQuery()->forGroupIds($this->getAdminUser()->getGroupsIds());

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    private function getAdminUser(): UserInterface
    {
        return $this->get('pim_user.repository.user')->findOneByIdentifier('admin');
    }

    private function getQuery(): GetEditableCategoryCodes
    {
        return $this->get('akeneo.pim.enrichment.category.get_editable_category_codes');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
