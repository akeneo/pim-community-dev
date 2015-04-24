<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Common\Filter;

use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Repository\CategoryRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductCategoryRepositoryInterface;

class CategoryFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb, CategoryRepositoryInterface $categoryRepo, ProductCategoryRepositoryInterface $productRepo, ObjectIdResolverInterface $objectIdResolver)
    {
        $operators = ['IN', 'NOT IN', 'UNCLASSIFIED', 'IN OR UNCLASSIFIED', 'IN CHILDREN', 'NOT IN CHILDREN'];
        $this->beConstructedWith($categoryRepo, $productRepo, $objectIdResolver, ['categories'], $operators);
        $this->setQueryBuilder($qb);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterInterface');
    }

    function it_supports_operators()
    {
        $operators = ['IN', 'NOT IN', 'UNCLASSIFIED', 'IN OR UNCLASSIFIED', 'IN CHILDREN', 'NOT IN CHILDREN'];
        $this->getOperators()->shouldReturn($operators);
        $this->supportsOperator('IN')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_checks_if_field_is_supported()
    {
        $this->supportsField('categories')->shouldReturn(true);
        $this->supportsField('groups')->shouldReturn(false);
    }

    function it_adds_a_in_filter_on_categories_in_the_query($qb, $productRepo)
    {
        $productRepo->applyFilterByCategoryIds($qb, [42, 84], true)->shouldBeCalled();
        $this->addFieldFilter('categories', 'IN', [42, 84]);
    }

    function it_adds_a_not_in_filter_on_categories_in_the_query($qb, $productRepo)
    {
        $productRepo->applyFilterByCategoryIds($qb, [42, 84], false)->shouldBeCalled();
        $this->addFieldFilter('categories', 'NOT IN', [42, 84]);
    }

    function it_adds_a_in_children_filter_on_categories_in_the_query($qb, $productRepo, $categoryRepo, CategoryInterface $parent)
    {
        $categoryRepo->find(21)->shouldBeCalled()->willReturn($parent);
        $parent->getId()->willReturn(21);
        $categoryRepo->getAllChildrenIds($parent)->shouldBeCalled()->willReturn([42, 84]);
        $productRepo->applyFilterByCategoryIds($qb, [42, 84, 21], true)->shouldBeCalled();
        $this->addFieldFilter('categories', 'IN CHILDREN', [21]);
    }

    function it_adds_a_in_children_filter_with_many_parents_on_categories_in_the_query($qb, $productRepo, $categoryRepo, CategoryInterface $parent, CategoryInterface $anotherParent)
    {
        $categoryRepo->find(21)->shouldBeCalled()->willReturn($parent);
        $parent->getId()->willReturn(21);
        $categoryRepo->getAllChildrenIds($parent)->shouldBeCalled()->willReturn([42, 84]);

        $categoryRepo->find(2)->shouldBeCalled()->willReturn($anotherParent);
        $anotherParent->getId()->willReturn(2);
        $categoryRepo->getAllChildrenIds($anotherParent)->shouldBeCalled()->willReturn([4, 8]);

        $productRepo->applyFilterByCategoryIds($qb, [42, 84, 21, 4, 8, 2], true)->shouldBeCalled();
        $this->addFieldFilter('categories', 'IN CHILDREN', [21, 2]);
    }

    function it_adds_a_not_in_children_filter_on_categories_in_the_query($qb, $productRepo, $categoryRepo, CategoryInterface $parent)
    {
        $categoryRepo->find(21)->shouldBeCalled()->willReturn($parent);
        $parent->getId()->willReturn(21);
        $categoryRepo->getAllChildrenIds($parent)->shouldBeCalled()->willReturn([42, 84]);
        $productRepo->applyFilterByCategoryIds($qb, [42, 84, 21], false)->shouldBeCalled();
        $this->addFieldFilter('categories', 'NOT IN CHILDREN', [21]);
    }

    function it_adds_a_unclassified_filter_on_categories_in_the_query($qb, $productRepo)
    {
        $productRepo->applyFilterByUnclassified($qb)->shouldBeCalled();
        $this->addFieldFilter('categories', 'UNCLASSIFIED', []);
    }

    function it_adds_a_in_or_unclassified_filter_on_categories_in_the_query($qb, $productRepo)
    {
        $productRepo->applyFilterByCategoryIdsOrUnclassified($qb, [42, 84])->shouldBeCalled();
        $this->addFieldFilter('categories', 'IN OR UNCLASSIFIED', [42, 84]);
    }
}
