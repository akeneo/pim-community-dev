<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\AncestorCodeFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use PhpSpec\ObjectBehavior;

class AncestorCodeFilterSpec extends ObjectBehavior
{
    function let(
        ProductModelRepositoryInterface $productModelRepository
    )
    {
        $this->beConstructedWith($productModelRepository, ['ancestor.code'], [Operators::EQUALS, Operators::IN_LIST, Operators::NOT_IN_LIST]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AncestorCodeFilter::class);
    }

    function it_is_a_filter()
    {
        $this->shouldImplement(FieldFilterInterface::class);
    }

    function it_supports_operators()
    {
        $this->supportsOperator(Operators::EQUALS)->shouldReturn(true);
        $this->supportsOperator(Operators::IN_LIST)->shouldReturn(true);
        $this->supportsOperator(Operators::NOT_IN_LIST)->shouldReturn(true);
        $this->supportsOperator(Operators::IS_EMPTY)->shouldReturn(false);
        $this->supportsOperator(Operators::IS_NOT_EMPTY)->shouldReturn(false);
    }

    function it_supports_ancestor_code_field()
    {
        $this->supportsField('ancestor.id')->shouldReturn(false);
        $this->supportsField('ancestor.code')->shouldReturn(true);
        $this->supportsField('wrong_field')->shouldReturn(false);
    }

    function it_adds_a_filter_with_operator_equals(
        SearchQueryBuilder $sqb
    )
    {
        $sqb->addFilter(
            [
                'terms' => ['ancestors.codes' => ['product_model_1', 'product_model_2']],
            ],
            )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter(
            'ancestor.code',
            Operators::EQUALS,
            ['product_model_1', 'product_model_2'],
            null,
            null,
            []
        );
    }

    function it_adds_a_filter_with_operator_in_list(
        SearchQueryBuilder $sqb
    )
    {
        $sqb->addFilter(
            [
                'terms' => ['ancestors.codes' => ['product_model_1', 'product_model_2']],
            ],
            )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter(
            'ancestor.code',
            Operators::IN_LIST,
            ['product_model_1', 'product_model_2'],
            null,
            null,
            []
        );
    }

    function it_adds_a_filter_with_operator_not_in_list(
        SearchQueryBuilder $sqb
    )
    {
        $sqb->addMustNot(
            [
                'terms' => ['ancestors.codes' => ['product_model_1', 'product_model_2']],
            ]
        )->shouldBeCalled();

        $this->setQueryBuilder($sqb);
        $this->addFieldFilter(
            'ancestor.code',
            Operators::NOT_IN_LIST,
            ['product_model_1', 'product_model_2'],
            null,
            null,
            []
        );
    }
}
