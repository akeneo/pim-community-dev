<?php

namespace spec\Pim\Bundle\FilterBundle\Filter;

use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;
use Pim\Component\Catalog\Model\CategoryInterface;
use Symfony\Component\Form\FormFactoryInterface;

class CategoryFilterSpec extends ObjectBehavior
{
    function let(
        FormFactoryInterface $factory,
        ProductFilterUtility $utility,
        CategoryRepositoryInterface $categoryRepo
    ) {
        $this->beConstructedWith($factory, $utility, $categoryRepo);
    }

    function it_is_an_oro_number_filter()
    {
        $this->shouldBeAnInstanceOf('Oro\Bundle\FilterBundle\Filter\NumberFilter');
    }

    function it_applies_a_filter_on_all_products(FilterDatasourceAdapterInterface $datasource)
    {
        $this->apply($datasource, ['value' => ['categoryId' => -2]])->shouldReturn(true);
    }

    function it_applies_a_filter_by_unclassified_products(
        $utility,
        $categoryRepo,
        FilterDatasourceAdapterInterface $datasource,
        CategoryInterface $tree
    ) {
        $tree->getId()->willReturn(1);
        $categoryRepo->find(1)->willReturn($tree);
        $categoryRepo->getAllChildrenCodes($tree)->willReturn(['bar', 'baz']);
        $utility->applyFilter($datasource, 'categories', 'NOT IN', ['bar', 'baz'])->shouldBeCalled();

        $this->apply($datasource, ['value' => ['categoryId' => -1, 'treeId' => 1]]);
    }

    function it_applies_a_filter_by_in_category(
        $utility,
        $categoryRepo,
        FilterDatasourceAdapterInterface $datasource,
        CategoryInterface $category
    ) {
        $categoryRepo->find(42)->willReturn($category);
        $category->getCode()->willReturn('foo');
        $utility->applyFilter($datasource, 'categories', 'IN', ['foo'])->shouldBeCalled();

        $this->apply($datasource, ['value' => ['categoryId' => 42], 'type' => false]);
    }

    function it_applies_a_filter_by_in_category_with_children(
        $utility,
        $categoryRepo,
        FilterDatasourceAdapterInterface $datasource,
        CategoryInterface $category
    ) {
        $categoryRepo->find(42)->willReturn($category);
        $category->getCode()->willReturn('foo');
        $categoryRepo->find(42)->willReturn($category);
        $categoryRepo->getAllChildrenCodes($category)->willReturn(['bar', 'baz']);

        $utility->applyFilter($datasource, 'categories', 'IN', ['bar', 'baz', 'foo'])->shouldBeCalled();

        $this->apply($datasource, ['value' => ['categoryId' => 42], 'type' => true]);
    }
}
