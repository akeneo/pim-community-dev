<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\Enrichment\Storage\Sql\Category;

use Akeneo\Pim\Permission\Bundle\Enrichment\Storage\Sql\Category\GetViewableCategoryCodes;
use Akeneo\Test\Integration\TestCase;
use AkeneoTestEnterprise\Pim\Permission\Integration\Enrichment\Storage\ElasticsearchAndSql\CategoryTree\CategoryTreeFixturesLoaderWithPermission;

/**
 * @author    Anaël CHARDAN <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetViewableCategoryCodesIntegration extends TestCase
{
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
            'tree_1_child_1_level_3',
            'tree_1_child_1_level_2',
            'tree_1_child_3_level_2'
        ]);
    }

    public function testGetAllCategories(): void
    {

        $expected = ['tree_1_child_1_level_3', 'tree_1_child_1_level_2'];
        $actual = $this->getQuery()->forCategoryCodes(
            $this->getAdminUserId(),
            ['tree_1_child_1_level_3', 'tree_2_child_1_level_2', 'tree_1_child_1_level_2', 'tree_2_child_1_level_2']
        );

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testGetNoCategories(): void
    {
        $expected = [];
        $actual = $this->getQuery()->forCategoryCodes(
            $this->getAdminUserId(),
            ['tree_2_child_1_level_2', 'tree_1_child_3_level_1']
        );

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    private function getAdminUserId(): int
    {
        return $this->testKernel->getContainer()->get('pim_user.repository.user')->findOneByIdentifier('admin')->getId();
    }

    private function getQuery(): GetViewableCategoryCodes
    {
        return $this->testKernel->getContainer()->get('akeneo.pim.enrichment.category.get_viewable_category_codes');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
