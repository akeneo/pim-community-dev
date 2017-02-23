<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\Common\Filter;

use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Filter\ObjectIdResolverInterface;
use Pim\Component\Catalog\Model\CategoryInterface;
use Akeneo\Component\Classification\Repository\CategoryFilterableRepositoryInterface;
use Akeneo\Component\Classification\Repository\CategoryRepositoryInterface;
use Prophecy\Argument;

class CategoryFilterSpec extends ObjectBehavior
{
    function let(QueryBuilder $qb, CategoryRepositoryInterface $categoryRepo, CategoryFilterableRepositoryInterface $itemRepo, ObjectIdResolverInterface $objectIdResolver)
    {
        $operators = ['IN', 'NOT IN', 'UNCLASSIFIED', 'IN OR UNCLASSIFIED', 'IN CHILDREN', 'NOT IN CHILDREN'];
        $this->beConstructedWith($categoryRepo, $itemRepo, $objectIdResolver, ['categories'], $operators);
        $this->setQueryBuilder($qb);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement('Pim\Component\Catalog\Query\Filter\FieldFilterInterface');
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

    function it_adds_a_filter_on_category_codes_by_default($qb, $itemRepo, $objectIdResolver)
    {
        $itemRepo->applyFilterByCategoryIds($qb, [42, 84], true)->shouldBeCalled();
        $objectIdResolver->getIdsFromCodes('category', ['foo', 'bar'])->willReturn([42, 84]);

        $this->addFieldFilter('categories', 'IN', ['foo', 'bar']);
    }

    function it_adds_a_in_filter_on_categories_in_the_query($qb, $itemRepo, $objectIdResolver)
    {
        $itemRepo->applyFilterByCategoryIds($qb, [42, 84], true)->shouldBeCalled();
        $objectIdResolver->getIdsFromCodes('category', ['foo', 'bar'])->willReturn([42, 84]);

        $this->addFieldFilter('categories', 'IN', ['foo', 'bar']);
    }

    function it_adds_a_not_in_filter_on_categories_in_the_query($qb, $itemRepo, $objectIdResolver)
    {
        $itemRepo->applyFilterByCategoryIds($qb, [42, 84], false)->shouldBeCalled();
        $objectIdResolver->getIdsFromCodes('category', ['foo', 'bar'])->willReturn([42, 84]);

        $this->addFieldFilter('categories', 'NOT IN', ['foo', 'bar']);
    }

    function it_adds_a_in_children_filter_on_categories_in_the_query($qb, $itemRepo, $categoryRepo, $objectIdResolver, CategoryInterface $parent)
    {
        $categoryRepo->find(21)->shouldBeCalled()->willReturn($parent);
        $parent->getId()->willReturn(21);
        $categoryRepo->getAllChildrenIds($parent)->shouldBeCalled()->willReturn([42, 84]);
        $itemRepo->applyFilterByCategoryIds($qb, [42, 84, 21], true)->shouldBeCalled();
        $objectIdResolver->getIdsFromCodes('category', ['baz'])->willReturn([21]);

        $this->addFieldFilter('categories', 'IN CHILDREN', ['baz']);
    }

    function it_adds_a_in_children_filter_with_many_parents_on_categories_in_the_query($qb, $itemRepo, $categoryRepo, $objectIdResolver, CategoryInterface $parent, CategoryInterface $anotherParent)
    {
        $categoryRepo->find(21)->shouldBeCalled()->willReturn($parent);
        $parent->getId()->willReturn(21);
        $categoryRepo->getAllChildrenIds($parent)->shouldBeCalled()->willReturn([42, 84]);

        $categoryRepo->find(2)->shouldBeCalled()->willReturn($anotherParent);
        $anotherParent->getId()->willReturn(2);
        $categoryRepo->getAllChildrenIds($anotherParent)->shouldBeCalled()->willReturn([4, 8]);

        $itemRepo->applyFilterByCategoryIds($qb, [42, 84, 21, 4, 8, 2], true)->shouldBeCalled();
        $objectIdResolver->getIdsFromCodes('category', ['foo', 'bar'])->willReturn([21, 2]);

        $this->addFieldFilter('categories', 'IN CHILDREN', ['foo', 'bar']);
    }

    function it_adds_a_not_in_children_filter_on_categories_in_the_query($qb, $itemRepo, $categoryRepo, $objectIdResolver, CategoryInterface $parent)
    {
        $categoryRepo->find(21)->shouldBeCalled()->willReturn($parent);
        $parent->getId()->willReturn(21);
        $categoryRepo->getAllChildrenIds($parent)->shouldBeCalled()->willReturn([42, 84]);
        $itemRepo->applyFilterByCategoryIds($qb, [42, 84, 21], false)->shouldBeCalled();
        $objectIdResolver->getIdsFromCodes('category', ['foo'])->willReturn([21]);

        $this->addFieldFilter('categories', 'NOT IN CHILDREN', ['foo']);
    }

    function it_adds_a_unclassified_filter_on_categories_in_the_query($qb, $itemRepo)
    {
        $itemRepo->applyFilterByUnclassified($qb)->shouldBeCalled();
        $this->addFieldFilter('categories', 'UNCLASSIFIED', []);
    }

    function it_adds_a_in_or_unclassified_filter_on_categories_in_the_query($qb, $itemRepo, $objectIdResolver)
    {
        $itemRepo->applyFilterByCategoryIdsOrUnclassified($qb, [42, 84])->shouldBeCalled();
        $objectIdResolver->getIdsFromCodes('category', ['foo', 'bar'])->willReturn([42, 84]);

        $this->addFieldFilter('categories', 'IN OR UNCLASSIFIED', ['foo', 'bar']);
    }

    function it_returns_supported_fields()
    {
        $this->getFields()->shouldReturn(['categories']);
    }
}
