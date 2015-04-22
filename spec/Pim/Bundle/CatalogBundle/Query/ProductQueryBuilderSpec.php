<?php

namespace spec\Pim\Bundle\CatalogBundle\Query;

use Akeneo\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Query\Filter\FilterRegistryInterface;
use Pim\Bundle\CatalogBundle\Query\Sorter\AttributeSorterInterface;
use Pim\Bundle\CatalogBundle\Query\Sorter\FieldSorterInterface;
use Pim\Bundle\CatalogBundle\Query\Sorter\SorterRegistryInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;

class ProductQueryBuilderSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $repository,
        FilterRegistryInterface $filterRegistry,
        SorterRegistryInterface $sorterRegistry,
        CursorFactoryInterface $cursorFactory,
        QueryBuilder $qb
    ) {
        $this->beConstructedWith(
            $repository,
            $filterRegistry,
            $sorterRegistry,
            $cursorFactory,
            ['locale' => 'en_US', 'scope' => 'print']
        );
        $this->setQueryBuilder($qb);
    }

    function it_is_a_product_query_builder()
    {
        $this->shouldImplement('Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderInterface');
    }

    function it_adds_a_field_filter($repository, $filterRegistry, FieldFilterInterface $filter)
    {
        $repository->findOneBy(['code' => 'id'])->willReturn(null);
        $filterRegistry->getFieldFilter('id')->willReturn($filter);
        $filter->supportsOperator('=')->willReturn(true);
        $filter->setQueryBuilder(Argument::any())->shouldBeCalled();
        $filter->addFieldFilter('id', '=', '42', 'en_US', 'print')->shouldBeCalled();

        $this->addFilter('id', '=', '42', []);
    }

    function it_adds_an_attribute_filter(
        $repository,
        $filterRegistry,
        AttributeFilterInterface $filter,
        AttributeInterface $attribute
    ) {
        $repository->findOneBy(['code' => 'sku'])->willReturn($attribute);
        $filterRegistry->getAttributeFilter($attribute)->willReturn($filter);
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $filter->supportsOperator('=')->willReturn(true);
        $filter->setQueryBuilder(Argument::any())->shouldBeCalled();
        $filter->addAttributeFilter(
            $attribute,
            '=', '42',
            'en_US',
            'print',
            ['locale' => 'en_US', 'scope' => 'print', 'field' => 'sku']
        )->shouldBeCalled();

        $this->addFilter('sku', '=', '42', []);
    }

    function it_adds_a_field_sorter($repository, $sorterRegistry, FieldSorterInterface $sorter)
    {
        $repository->findOneBy(['code' => 'id'])->willReturn(null);
        $sorterRegistry->getFieldSorter('id')->willReturn($sorter);
        $sorter->setQueryBuilder(Argument::any())->shouldBeCalled();
        $sorter->addFieldSorter('id', 'DESC', 'en_US', 'print')->shouldBeCalled();

        $this->addSorter('id', 'DESC', []);
    }

    function it_adds_an_attribute_sorter(
        $repository,
        $sorterRegistry,
        AttributeSorterInterface $sorter,
        AttributeInterface $attribute
    ) {
        $repository->findOneBy(['code' => 'sku'])->willReturn($attribute);
        $sorterRegistry->getAttributeSorter($attribute)->willReturn($sorter);
        $sorter->setQueryBuilder(Argument::any())->shouldBeCalled();
        $sorter->addAttributeSorter($attribute, 'DESC', 'en_US', 'print')->shouldBeCalled();

        $this->addSorter('sku', 'DESC', []);
    }

    function it_provides_a_query_builder_once_configured($qb)
    {
        $this->getQueryBuilder()->shouldReturn($qb);
    }

    function it_configures_the_query_builder($qb)
    {
        $this->setQueryBuilder($qb)->shouldReturn($this);
    }

    function it_executes_the_query(
        $qb,
        AbstractQuery $query,
        CursorFactoryInterface $cursorFactory,
        CursorInterface $cursor
    ) {
        $qb->getQuery()->willReturn($query);
        $cursorFactory->createCursor(Argument::any())->shouldBeCalled()->willReturn($cursor);

        $this->execute()->shouldReturn($cursor);
    }
}
