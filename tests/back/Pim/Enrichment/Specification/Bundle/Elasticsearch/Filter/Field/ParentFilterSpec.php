<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\AbstractFieldFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\ParentFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;

class ParentFilterSpec extends ObjectBehavior
{
    function let(ProductModelRepositoryInterface $productModelRepository)
    {
        $this->beConstructedWith($productModelRepository, ['parent'], ['EMPTY']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ParentFilter::class);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement(FieldFilterInterface::class);
        $this->shouldBeAnInstanceOf(AbstractFieldFilter::class);
    }

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(['EMPTY']);
        $this->supportsOperator('EMPTY')->shouldReturn(true);
        $this->supportsOperator('DOES NOT CONTAIN')->shouldReturn(false);
    }

    function it_supports_parent_field()
    {
        $this->supportsField('parent')->shouldReturn(true);
        $this->supportsField('a_not_supported_field')->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_is_empty(
        SearchQueryBuilder $sqb
    ) {
        $sqb->addMustNot(
            [
                'exists' => ['field' => 'parent'],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('parent', Operators::IS_EMPTY, null, null, null, []);
    }

    function it_adds_a_filter_with_operator_is_not_empty(
        SearchQueryBuilder $sqb
    ) {
        $sqb->addFilter(
            [
                'exists' => ['field' => 'parent'],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter('parent', Operators::IS_NOT_EMPTY, null, null, null, []);
    }

    function it_throws_an_exception_when_the_search_query_builder_is_not_initialized()
    {
        $this->shouldThrow(
            new \LogicException('The search query builder is not initialized in the filter.')
        )->during('addFieldFilter', ['family', Operators::IN_LIST, ['familyA'], null, null, []]);
    }

    function it_throws_an_exception_when_it_filters_on_an_unsupported_operator(
        SearchQueryBuilder $sqb
    ) {
        $this->setQueryBuilder($sqb);

        $this->shouldThrow(
            InvalidOperatorException::notSupported(
                'IN CHILDREN',
                ParentFilter::class
            )
        )->during('addFieldFilter', ['parent', Operators::IN_CHILDREN_LIST, null, null, null, []]);
    }
}
