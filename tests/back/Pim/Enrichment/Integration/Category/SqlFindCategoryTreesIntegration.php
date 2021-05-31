<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Category;

use Akeneo\Pim\Enrichment\Component\Category\Query\PublicApi\CategoryTree;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Category\SqlFindCategoryTrees;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class SqlFindCategoryTreesIntegration extends TestCase
{
    public SqlFindCategoryTrees $sqlFindCategoryTrees;

    public function setUp(): void
    {
        parent::setUp();
        $this->sqlFindCategoryTrees = $this->get('akeneo.enrichment.public_api.find_category_trees');
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
