<?php

namespace spec\Pim\Component\Catalog\Query;

use Akeneo\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\FilterRegistryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderOptionsResolverInterface;
use Pim\Component\Catalog\Query\Sorter\AttributeSorterInterface;
use Pim\Component\Catalog\Query\Sorter\FieldSorterInterface;
use Pim\Component\Catalog\Query\Sorter\SorterRegistryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;

class ProductQueryBuilderSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $repository,
        FilterRegistryInterface $filterRegistry,
        SorterRegistryInterface $sorterRegistry,
        CursorFactoryInterface $cursorFactory,
        SearchQueryBuilder $searchQb,
        ProductQueryBuilderOptionsResolverInterface $optionsResolver
    ) {
        $defaultContext = ['locale' => 'en_US', 'scope' => 'print'];
        $this->beConstructedWith(
            $repository,
            $filterRegistry,
            $sorterRegistry,
            $cursorFactory,
            $optionsResolver,
            $defaultContext
        );
        $optionsResolver->resolve($defaultContext)->willReturn($defaultContext);
        $this->setQueryBuilder($searchQb);
    }

    function it_is_a_product_query_builder()
    {
        $this->shouldImplement(ProductQueryBuilderInterface::class);
    }

    function it_adds_a_field_filter($repository, $filterRegistry, FieldFilterInterface $filter)
    {
        $repository->findOneByIdentifier('id')->willReturn(null);
        $filterRegistry->getFieldFilter('id', '=')->willReturn($filter);
        $filter->setQueryBuilder(Argument::any())->shouldBeCalled();
        $filter->addFieldFilter(
            'id',
            '=',
            '42',
            'en_US',
            'print',
            ['locale' => 'en_US', 'scope' => 'print']
        )->shouldBeCalled();

        $this->addFilter('id', '=', '42', []);
    }

    function it_adds_a_field_filter_even_if_an_attribute_is_similar(
        $repository,
        $filterRegistry,
        AttributeInterface $attribute,
        FieldFilterInterface $filter
    ) {
        $repository->findOneByIdentifier('id')->willReturn($attribute);
        $attribute->getCode()->willReturn('ID');
        $filterRegistry->getFieldFilter('id', '=')->willReturn($filter);
        $filter->setQueryBuilder(Argument::any())->shouldBeCalled();
        $filter->addFieldFilter(
            'id',
            '=',
            '42',
            'en_US',
            'print',
            ['locale' => 'en_US', 'scope' => 'print']
        )->shouldBeCalled();

        $this->addFilter('id', '=', '42', []);
    }

    function it_adds_an_attribute_filter(
        $repository,
        $filterRegistry,
        AttributeFilterInterface $filter,
        AttributeInterface $attribute
    ) {
        $repository->findOneByIdentifier('sku')->willReturn($attribute);
        $attribute->getCode()->willReturn('sku');
        $filterRegistry->getAttributeFilter($attribute, '=')->willReturn($filter);
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
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

    function it_provides_a_query_builder_once_configured($searchQb)
    {
        $this->getQueryBuilder()->shouldReturn($searchQb);
    }

    function it_configures_the_query_builder($searchQb)
    {
        $this->setQueryBuilder($searchQb)->shouldReturn($this);
    }

    function it_executes_the_query(
        $searchQb,
        CursorFactoryInterface $cursorFactory,
        CursorInterface $cursor
    ) {
        $searchQb->getQuery()->willReturn([]);
        $cursorFactory->createCursor(Argument::any(), [] )->shouldBeCalled()->willReturn($cursor);

        $this->execute()->shouldReturn($cursor);
    }

    function it_provides_the_raw_filters(
        $repository,
        $filterRegistry,
        FieldFilterInterface $filterField,
        AttributeFilterInterface $filterAttribute,
        AttributeInterface $attribute
    ) {
        $repository->findOneByIdentifier('id')->willReturn(null);
        $filterRegistry->getFieldFilter('id', '=')->willReturn($filterField);

        $attribute->getCode()->willReturn('bar');
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $repository->findOneByIdentifier('bar')->willReturn($attribute);
        $filterRegistry->getAttributeFilter($attribute, 'IN LIST')->willReturn($filterAttribute);

        $this->addFilter('id', '=', '42', []);
        $this->addFilter('bar', 'IN LIST', ['titi', 'tutu'], []);

        $this->getRawFilters()->shouldReturn(
            [
                [
                    'field'    => 'id',
                    'operator' => '=',
                    'value'    => '42',
                    'context'  => ['locale' => 'en_US', 'scope' => 'print'],
                    'type'     => 'field'
                ],
                [
                    'field'    => 'bar',
                    'operator' => 'IN LIST',
                    'value'    => ['titi', 'tutu'],
                    'context'  => ['locale' => 'en_US', 'scope' => 'print', 'field' => 'bar'],
                    'type'     => 'attribute'
                ],
            ]
        );
    }

}
