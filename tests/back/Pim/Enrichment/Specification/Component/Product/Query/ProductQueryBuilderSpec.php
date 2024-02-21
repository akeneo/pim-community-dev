<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Query;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderOptionsResolverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\AttributeSorterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\FieldSorterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\SorterRegistryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use PhpSpec\ObjectBehavior;
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

    function it_adds_an_entity_type_filter(
        CursorFactoryInterface $cursorFactory,
        CursorInterface $cursor,
        FieldFilterInterface $filterField,
        FilterRegistryInterface $filterRegistry,
        SearchQueryBuilder $searchQb,
        SorterRegistryInterface $sorterRegistry,
        FieldSorterInterface $sorter
    ) {
        $filterRegistry->getFieldFilter('entity_type', '=')->willReturn($filterField);
        $cursorFactory->createCursor(Argument::any(), [] )->shouldBeCalled()->willReturn($cursor);
        $filterField->setQueryBuilder(Argument::any())->shouldBeCalled();

        $filterField->addFieldFilter(
            "entity_type",
            Operators::EQUALS,
            ProductInterface::class,
            "en_US",
            "print",
            ["locale" => "en_US", "scope" => "print"]
        )->shouldBeCalled();

        $searchQb->getQuery(Argument::any())->shouldBeCalledOnce()->willReturn([]);
        $searchQb->hasSort('identifier')->willReturn(false);
        $searchQb->hasSort('id')->willReturn(false);
        $sorterRegistry->getFieldSorter('identifier')->willReturn($sorter);
        $sorter->setQueryBuilder(Argument::any())->shouldBeCalled();
        $sorter->addFieldSorter('identifier', Argument::cetera())->willReturn($sorter);

        $this->execute()->shouldReturn($cursor);
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
        $filterRegistry->getFieldFilter('sku', '=')->willReturn(null);
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
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(false);

        $repository->findOneBy(['code' => 'sku'])->willReturn($attribute);
        $sorterRegistry->getAttributeSorter($attribute)->willReturn($sorter);
        $sorter->setQueryBuilder(Argument::any())->shouldBeCalled();
        $sorter->addAttributeSorter($attribute, 'DESC', null, null)->shouldBeCalled();

        $this->addSorter('sku', 'DESC', []);
    }

    function it_adds_an_attribute_sorter_on_localizable_attribute(
        $repository,
        $sorterRegistry,
        AttributeSorterInterface $sorter,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isLocaleSpecific()->willReturn(false);

        $repository->findOneBy(['code' => 'name'])->willReturn($attribute);
        $sorterRegistry->getAttributeSorter($attribute)->willReturn($sorter);
        $sorter->setQueryBuilder(Argument::any())->shouldBeCalled();
        $sorter->addAttributeSorter($attribute, 'DESC', 'de_DE', null)->shouldBeCalled();

        $this->addSorter('name', 'DESC', ['locale' => 'de_DE', 'scope' => 'ecommerce']);
    }

    function it_adds_an_attribute_sorter_on_local_specific_attribute(
        $repository,
        $sorterRegistry,
        AttributeSorterInterface $sorter,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(false);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(true);

        $repository->findOneBy(['code' => 'name'])->willReturn($attribute);
        $sorterRegistry->getAttributeSorter($attribute)->willReturn($sorter);
        $sorter->setQueryBuilder(Argument::any())->shouldBeCalled();
        $sorter->addAttributeSorter($attribute, 'DESC', 'de_DE', null)->shouldBeCalled();

        $this->addSorter('name', 'DESC', ['locale' => 'de_DE', 'scope' => 'ecommerce']);
    }

    function it_adds_an_attribute_sorter_on_scopable_attribute(
        $repository,
        $sorterRegistry,
        AttributeSorterInterface $sorter,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isLocaleSpecific()->willReturn(false);

        $repository->findOneBy(['code' => 'name'])->willReturn($attribute);
        $sorterRegistry->getAttributeSorter($attribute)->willReturn($sorter);
        $sorter->setQueryBuilder(Argument::any())->shouldBeCalled();
        $sorter->addAttributeSorter($attribute, 'DESC', null, 'ecommerce')->shouldBeCalled();

        $this->addSorter('name', 'DESC', ['locale' => 'de_DE', 'scope' => 'ecommerce']);
    }

    function it_adds_an_attribute_sorter_on_scopable_and_localizable_attribute(
        $repository,
        $sorterRegistry,
        AttributeSorterInterface $sorter,
        AttributeInterface $attribute
    ) {
        $attribute->isScopable()->willReturn(true);
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isLocaleSpecific()->willReturn(false);

        $repository->findOneBy(['code' => 'name'])->willReturn($attribute);
        $sorterRegistry->getAttributeSorter($attribute)->willReturn($sorter);
        $sorter->setQueryBuilder(Argument::any())->shouldBeCalled();
        $sorter->addAttributeSorter($attribute, 'DESC', 'de_DE', 'ecommerce')->shouldBeCalled();

        $this->addSorter('name', 'DESC', ['locale' => 'de_DE', 'scope' => 'ecommerce']);
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
        CursorInterface $cursor,
        FieldFilterInterface $filterField,
        FilterRegistryInterface $filterRegistry
    ) {
        $filterRegistry->getFieldFilter('entity_type', '=')->willReturn($filterField);
        $searchQb->getQuery()->willReturn([]);
        $cursorFactory->createCursor(Argument::any(), [] )->shouldBeCalled()->willReturn($cursor);

        $searchQb->getQuery(Argument::any())->shouldBeCalledOnce()->willReturn([]);
        $searchQb->hasSort('identifier')->willReturn(true);
        $searchQb->hasSort('id')->willReturn(false);

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
        $filterRegistry->getFieldFilter('bar', 'IN LIST')->willReturn(null);
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
