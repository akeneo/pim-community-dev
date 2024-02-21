<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Integration;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Category\CountTreesChildren;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CountTressChildrenIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_counts_the_number_of_children_by_tree()
    {
        $emptyCategories = $this->get(CountTreesChildren::class)->execute();

        $this->assertEquals(['master' => 0], $emptyCategories);

        $this->createCategory(['code' => 'a_tree']);
        $this->createCategory(['code' => 'master_child_A', 'parent' => 'master']);
        $this->createCategory(['code' => 'a_tree_child_A', 'parent' => 'a_tree']);
        $this->createCategory(['code' => 'a_tree_child_B', 'parent' => 'a_tree']);
        $this->createCategory(['code' => 'a_tree_child_C', 'parent' => 'a_tree_child_B']);
        $this->createCategory(['code' => 'a_tree_child_D', 'parent' => 'a_tree_child_C']);

        $result = $this->get(CountTreesChildren::class)->execute();

        $expectedResult = [
            'master' => 1,
            'a_tree' => 4,
        ];

        $this->assertEqualsCanonicalizing($expectedResult, $result);
    }
}
