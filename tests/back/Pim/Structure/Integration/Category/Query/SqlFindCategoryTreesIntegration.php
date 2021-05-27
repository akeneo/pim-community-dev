<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Category;

use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Category\Sql\SqlCountTotalCategoriesPerTree;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Category\CategoryTree;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class SqlFindCategoryTreesIntegration extends TestCase
{
    public SqlCountTotalCategoriesPerTree $sqlFindCategoryTrees;

    public function setUp(): void
    {
        parent::setUp();
        $this->sqlFindCategoryTrees = $this->get('akeneo.pim.structure.query.find_category_trees');
    }

    /** @test */
    public function it_fetches_the_category_trees(): void
    {
        $masterTree = new CategoryTree();
        $masterTree->code = 'master';
        $masterTree->labels = ['en_US' => 'Master catalog'];

        $actual = $this->sqlFindCategoryTrees->execute();

        $expected = [$masterTree];
        $this->assertEquals($expected, $actual);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
