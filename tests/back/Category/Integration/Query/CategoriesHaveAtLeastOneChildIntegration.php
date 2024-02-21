<?php

declare(strict_types=1);

namespace AkeneoTest\Category\Integration\Query;

use Akeneo\Category\ServiceApi\Query\CategoriesHaveAtLeastOneChild;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CategoriesHaveAtLeastOneChildIntegration extends TestCase
{
    /**
     * @see https://en.wikipedia.org/wiki/Nested_set_model
     *
     * +-----------------------+------+-----+-----+
     * | code                  | root | lft | rgt |
     * +-----------------------+------+-----+-----+
     * | master                |    0 |   1 |   2 |
     * | root1                 |   50 |   1 |  12 |
     * | ├─ root1-A            |   50 |   2 |   7 |
     * | │  ├─ root1-A-a       |   50 |   8 |   9 |
     * | │  ├─ root1-A-b       |   50 |  10 |  11 |
     * | ├─ root1-B            |   50 |   3 |   4 |
     * | ├─ root1-C            |   50 |   5 |   6 |
     * | root2                 |  100 |   1 |   6 |
     * | ├─ root2-A            |  100 |   2 |   3 |
     * | ├─ root2-B            |  100 |   4 |   5 |
     * +-----------------------+------+-----+-----+
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createCategory(['code' => 'root1']);
        $this->createCategory(['code' => 'root1-A', 'parent' => 'root1']);
        $this->createCategory(['code' => 'root1-B', 'parent' => 'root1']);
        $this->createCategory(['code' => 'root1-C', 'parent' => 'root1']);
        $this->createCategory(['code' => 'root1-A-a', 'parent' => 'root1-A']);
        $this->createCategory(['code' => 'root1-A-b', 'parent' => 'root1-A']);

        $this->createCategory(['code' => 'root2']);
        $this->createCategory(['code' => 'root2-A', 'parent' => 'root2']);
        $this->createCategory(['code' => 'root2-B', 'parent' => 'root2']);
    }

    /** @test */
    public function it_should_return_true_with_single_items()
    {
        $categoryHierarchy = $this->getCategoriesHaveAtLeastOneChild();
        $this->assertTrue($categoryHierarchy->among(['root1'], ['root1']));
        $this->assertTrue($categoryHierarchy->among(['root1'], ['root1-A']));
        $this->assertTrue($categoryHierarchy->among(['root1'], ['root1-A-a']));
        $this->assertTrue($categoryHierarchy->among(['root1-A'], ['root1-A-a']));
        $this->assertTrue($categoryHierarchy->among(['root2'], ['root2-A']));
    }

    /** @test */
    public function it_should_return_false_with_single_items()
    {
        $categoryHierarchy = $this->getCategoriesHaveAtLeastOneChild();
        $this->assertFalse($categoryHierarchy->among(['root1'], ['root2-A']));
        $this->assertFalse($categoryHierarchy->among(['root1-B'], ['root2-A']));
        $this->assertFalse($categoryHierarchy->among(['root1'], []));
        $this->assertFalse($categoryHierarchy->among([], ['root2-A']));
        $this->assertFalse($categoryHierarchy->among(['unknown_parent'], ['root1-A-a']));
        $this->assertFalse($categoryHierarchy->among(['root1'], ['unknown_child']));
    }

    /** @test */
    public function it_should_return_true_if_any_item_is_true()
    {
        $categoryHierarchy = $this->getCategoriesHaveAtLeastOneChild();
        $this->assertTrue($categoryHierarchy->among(['root1', 'root1-A'], ['root1-A', 'root1-A-a']));
        $this->assertTrue($categoryHierarchy->among(['root2', 'root1'], ['root1-A-a']));
        $this->assertTrue($categoryHierarchy->among(['root1'], ['root2-A', 'root1-A-a']));
    }

    /** @test */
    public function it_should_return_false_if_all_items_are_false()
    {
        $categoryHierarchy = $this->getCategoriesHaveAtLeastOneChild();
        $this->assertFalse($categoryHierarchy->among(['root1-B'], ['root1-A', 'root1-A-a', 'root2-A']));
        $this->assertFalse($categoryHierarchy->among(['root1-A', 'root2'], ['root1-B-a']));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getCategoriesHaveAtLeastOneChild(): CategoriesHaveAtLeastOneChild
    {
        return $this->get(CategoriesHaveAtLeastOneChild::class);
    }
}
