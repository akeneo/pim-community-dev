<?php

namespace spec\Oro\Bundle\PimFilterBundle\Filter;

use Oro\Bundle\FilterBundle\Filter\NumberFilter;
use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Oro\Bundle\PimFilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
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
        $this->shouldBeAnInstanceOf(NumberFilter::class);
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
        $tree->getCode()->willReturn('my_tree');
        $categoryRepo->find(1)->willReturn($tree);
        $utility->applyFilter($datasource, 'categories', 'NOT IN CHILDREN', ['my_tree'])->shouldBeCalled();

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

        $utility->applyFilter($datasource, 'categories', 'IN CHILDREN', ['foo'])->shouldBeCalled();

        $this->apply($datasource, ['value' => ['categoryId' => 42], 'type' => true]);
    }
}
