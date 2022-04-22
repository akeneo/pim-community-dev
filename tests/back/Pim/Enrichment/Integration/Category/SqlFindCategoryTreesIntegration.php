<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Category;

use Akeneo\Category\Infrastructure\Component\Query\PublicApi\CategoryTree;
use Akeneo\Category\Infrastructure\Storage\Sql\SqlFindCategoryTrees;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class SqlFindCategoryTreesIntegration extends TestCase
{
    public SqlFindCategoryTrees $sqlFindCategoryTrees;

    public function setUp(): void
    {
        parent::setUp();
        $this->sqlFindCategoryTrees = $this->createQuery(new AllowAll());
    }

    /** @test */
    public function it_fetches_the_category_trees(): void
    {
        $masterTree = new CategoryTree();
        $masterTree->code = 'master';
        $masterTree->labels = ['en_US' => 'Master catalog'];

        $actual = $this->sqlFindCategoryTrees->execute();

        $expected = [$masterTree];
        self::assertEquals($expected, $actual);
    }

    /** @test */
    public function it_filters_the_category_trees(): void
    {
        $this->sqlFindCategoryTrees = $this->createQuery(new DenyAll());

        $actual = $this->sqlFindCategoryTrees->execute();

        self::assertEmpty($actual);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createQuery(CollectionFilterInterface $collectionFilter): SqlFindCategoryTrees
    {
        return new SqlFindCategoryTrees(
            $this->get('pim_catalog.repository.category'),
            $this->get('pim_catalog.normalizer.standard.translation'),
            $collectionFilter
        );
    }
}

class AllowAll implements CollectionFilterInterface
{
    public function filterCollection($collection, $type, array $options = [])
    {
        return $collection;
    }

    public function supportsCollection($collection, $type, array $options = [])
    {
        return true;
    }
}

class DenyAll implements CollectionFilterInterface
{
    public function filterCollection($collection, $type, array $options = [])
    {
        return [];
    }

    public function supportsCollection($collection, $type, array $options = [])
    {
        return true;
    }
}
