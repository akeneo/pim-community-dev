<?php

declare(strict_types=1);

namespace Akeneo\Test\Category\Integration;

use Akeneo\Category\Api\CategoryTree;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Category\SqlFindCategoryTrees;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class SqlFindCategoryTreesIntegration extends TestCase
{
    public SqlFindCategoryTrees $sqlFindCategoryTrees;

    public function setUp(): void
    {
        parent::setUp();
        $this->sqlFindCategoryTrees = $this->createQuery();
    }

    /** @test */
    public function it_fetches_the_category_trees(): void
    {
        $actual = $this->sqlFindCategoryTrees->execute();

        $masterTree = new CategoryTree();
        $masterTree->id = $actual[0]->id;
        $masterTree->code = 'master';
        $masterTree->labels = ['en_US' => 'Master catalog'];

        $expected = [$masterTree];
        self::assertEquals($expected, $actual);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createQuery(): SqlFindCategoryTrees
    {
        return new SqlFindCategoryTrees(
            $this->get('pim_catalog.repository.category'),
            $this->get('pim_catalog.normalizer.standard.translation')
        );
    }
}
