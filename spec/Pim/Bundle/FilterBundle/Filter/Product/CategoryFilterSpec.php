<?php

namespace spec\Pim\Bundle\FilterBundle\Filter\Product;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Repository\CategoryRepositoryInterface;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;
use Pim\Bundle\CatalogBundle\Manager\ProductCategoryManager;
use Symfony\Component\Form\FormFactoryInterface;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

class CategoryFilterSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, ProductFilterUtility $utility, ProductCategoryManager $manager)
    {
        $this->beConstructedWith($factory, $utility, $manager);
    }

    function it_is_an_oro_number_filter()
    {
        $this->shouldBeAnInstanceOf('Oro\Bundle\FilterBundle\Filter\NumberFilter');
    }

    function it_applies_a_filter_on_all_products(
        FilterDatasourceAdapterInterface $datasource,
        $utility
    ) {
        $this->apply($datasource, ['value' => ['categoryId' => -2]])->shouldReturn(true);
    }

    function it_applies_a_filter_by_unclassified_products(
        FilterDatasourceAdapterInterface $datasource,
        $utility,
        $manager,
        CategoryRepositoryInterface $repo,
        CategoryInterface $tree
    ) {
        $manager->getCategoryRepository()->willReturn($repo);
        $tree->getId()->willReturn(1);
        $repo->find(1)->willReturn($tree);
        $repo->getAllChildrenIds($tree)->willReturn([2, 3]);
        $utility->applyFilter($datasource, 'categories.id', 'NOT IN', [2, 3])->shouldBeCalled();

        $this->apply($datasource, ['value' => ['categoryId' => -1, 'treeId' => 1]]);
    }

    function it_applies_a_filter_by_in_category(
        FilterDatasourceAdapterInterface $datasource,
        $utility,
        $manager,
        CategoryRepositoryInterface $repo,
        CategoryInterface $category
    ) {
        $manager->getCategoryRepository()->willReturn($repo);
        $repo->find(42)->willReturn($category);
        $category->getId()->willReturn(42);
        $utility->applyFilter($datasource, 'categories.id', 'IN', [42])->shouldBeCalled();

        $this->apply($datasource, ['value' => ['categoryId' => 42], 'type' => false]);
    }

    function it_applies_a_filter_by_in_category_with_children(
        FilterDatasourceAdapterInterface $datasource,
        $utility,
        $manager,
        CategoryRepositoryInterface $repo,
        CategoryInterface $category
    ) {
        $manager->getCategoryRepository()->willReturn($repo);
        $repo->find(42)->willReturn($category);
        $category->getId()->willReturn(42);
        $repo->find(42)->willReturn($category);
        $repo->getAllChildrenIds($category)->willReturn([2, 3]);

        $utility->applyFilter($datasource, 'categories.id', 'IN', [2, 3, 42])->shouldBeCalled();

        $this->apply($datasource, ['value' => ['categoryId' => 42], 'type' => true]);
    }

}
