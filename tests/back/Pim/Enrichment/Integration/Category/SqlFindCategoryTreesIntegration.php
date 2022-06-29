<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Category;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
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
        $this->createCategory(['code' => 'sales']);
        $this->sqlFindCategoryTrees = $this->createQuery(new AllowAll());
    }

    /** @test */
    public function it_fetches_the_category_trees(): void
    {
        $actual = $this->sqlFindCategoryTrees->execute();

        $masterTree = new CategoryTree();
        $masterTree->id = $actual[0]->id;
        $masterTree->code = 'master';
        $masterTree->labels = ['en_US' => 'Master catalog'];

        $saleTree = new CategoryTree();
        $saleTree->id = $actual[1]->id;
        $saleTree->code = 'sales';
        $saleTree->labels = [];

        $expected = [$masterTree, $saleTree];
        self::assertEquals($expected, $actual);
    }

    /** @test */
    public function it_filters_the_category_trees(): void
    {
        $this->sqlFindCategoryTrees = $this->createQuery(new DenyAll());

        $actual = $this->sqlFindCategoryTrees->execute();

        self::assertEmpty($actual);
    }

    /** @test */
    public function it_does_not_apply_permission_on_category_trees(): void
    {
        $actual = $this->sqlFindCategoryTrees->execute(false);

        $masterTree = new CategoryTree();
        $masterTree->id = $actual[0]->id;
        $masterTree->code = 'master';
        $masterTree->labels = ['en_US' => 'Master catalog'];

        $saleTree = new CategoryTree();
        $saleTree->id = $actual[1]->id;
        $saleTree->code = 'sales';
        $saleTree->labels = [];

        $expected = [$masterTree, $saleTree];
        self::assertEquals($expected, $actual);
    }

    /** @test */
    public function it_applies_permission_on_category_trees(): void
    {
        $this->sqlFindCategoryTrees = $this->createQuery(new DenySalesCategory());
        
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

class DenySalesCategory implements CollectionFilterInterface
{
    public function filterCollection($collection, $type, array $options = [])
    {
        return array_filter($collection, fn (Category $category) => $category->getCode() != 'sales');
    }

    public function supportsCollection($collection, $type, array $options = [])
    {
        return true;
    }
}
