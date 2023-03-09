<?php

declare(strict_types=1);

namespace AkeneoTest\Category\Integration\Query;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Category\CountTreesChildren;
use Akeneo\Pim\Enrichment\Category\Infrastructure\Query\SqlGetExistingCategories;
use Akeneo\Pim\Enrichment\Category\Infrastructure\Query\SqlGetHierarchicalInfoCategories;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetHierarchicalInfoCategoriesIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createCategory(['code' => 'first']);
        $this->createCategory(['code' => 'first_child_A', 'parent' => 'first']);

        $this->createCategory(['code' => 'second']);
        $this->createCategory(['code' => 'second_child_A', 'parent' => 'second']);
        $this->createCategory(['code' => 'second_sub_child_A', 'parent' => 'second_child_A']);
    }

    /** @test */
    public function it_should_return_true_if_is_a_child()
    {
        $this->assertEquals(true, $this->getSqlGetHierarchicalInfoCategories()->isAChildOf('first', 'first_child_A'));
        $this->assertEquals(true, $this->getSqlGetHierarchicalInfoCategories()->isAChildOf('second', 'second_child_A'));
        $this->assertEquals(true, $this->getSqlGetHierarchicalInfoCategories()->isAChildOf('second', 'second_sub_child_A'));
        $this->assertEquals(true, $this->getSqlGetHierarchicalInfoCategories()->isAChildOf('second_child_A', 'second_sub_child_A'));
    }

    /** @test */
    public function it_should_return_false_if_is_not_a_child()
    {
        $this->assertEquals(false, $this->getSqlGetHierarchicalInfoCategories()->isAChildOf('first', 'second_child_A'));
        $this->assertEquals(false, $this->getSqlGetHierarchicalInfoCategories()->isAChildOf('first', 'second_sub_child_A'));
        $this->assertEquals(false, $this->getSqlGetHierarchicalInfoCategories()->isAChildOf('second_child_A', 'first_child_A'));
    }


    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function getSqlGetHierarchicalInfoCategories(): SqlGetHierarchicalInfoCategories
    {
        return $this->get(SqlGetHierarchicalInfoCategories::class);
    }
}
