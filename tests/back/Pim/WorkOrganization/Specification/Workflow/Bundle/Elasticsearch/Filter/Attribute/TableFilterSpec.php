<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ElasticsearchFilterValidator;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute\TableFilter;
use PhpSpec\ObjectBehavior;

class TableFilterSpec extends ObjectBehavior
{
    function let(ElasticsearchFilterValidator $filterValidator, SearchQueryBuilder $searchQueryBuilder)
    {
        $this->beConstructedWith($filterValidator);
        $this->setQueryBuilder($searchQueryBuilder);
    }

    function it_is_a_table_attribute_filter()
    {
        $this->shouldHaveType(TableFilter::class);
        $this->shouldImplement(AttributeFilterInterface::class);
    }

    function it_only_supports_table_attributes(AttributeInterface $nutrition, AttributeInterface $description)
    {
        $nutrition->getType()->willReturn(AttributeTypes::TABLE);
        $this->supportsAttribute($nutrition)->shouldBe(true);

        $description->getType()->willReturn(AttributeTypes::TEXTAREA);
        $this->supportsAttribute($description)->shouldBe(false);
    }

    function it_only_supports_not_empty_operator()
    {
        $this->supportsOperator(Operators::IS_NOT_EMPTY)->shouldBe(true);
        $this->supportsOperator(Operators::IS_EMPTY)->shouldBe(false);
        $this->supportsOperator(Operators::CONTAINS)->shouldBe(false);
    }

    function it_adds_a_filter_with_not_empty_operator_and_scopable_and_localizable_attribute(
        ElasticsearchFilterValidator $filterValidator,
        SearchQueryBuilder $searchQueryBuilder,
        AttributeInterface $nutrition
    ) {
        $nutrition->getType()->willReturn(AttributeTypes::TABLE);
        $nutrition->getCode()->willReturn('nutrition');
        $nutrition->getBackendType()->willReturn('table');
        $nutrition->isScopable()->willReturn(true);
        $nutrition->isLocalizable()->willReturn(true);

        $filterValidator->validateChannelForAttribute('nutrition', 'ecommerce')->shouldBeCalled();
        $filterValidator->validateLocaleForAttribute('nutrition', 'en_US')->shouldBeCalled();

        $searchQueryBuilder->addFilter([
            'exists' => [
                'field' => 'values.nutrition-table.ecommerce.en_US',
            ],
        ])->shouldBeCalled()->willReturn($searchQueryBuilder);

        $this->addAttributeFilter($nutrition, Operators::IS_NOT_EMPTY, null, 'en_US', 'ecommerce')->shouldReturn($this);
    }

    function it_adds_a_filter_with_not_empty_operator_and_scopable_attribute(
        ElasticsearchFilterValidator $filterValidator,
        SearchQueryBuilder $searchQueryBuilder,
        AttributeInterface $nutrition
    ) {
        $nutrition->getType()->willReturn(AttributeTypes::TABLE);
        $nutrition->getCode()->willReturn('nutrition');
        $nutrition->getBackendType()->willReturn('table');
        $nutrition->isScopable()->willReturn(true);
        $nutrition->isLocalizable()->willReturn(false);

        $filterValidator->validateChannelForAttribute('nutrition', 'ecommerce')->shouldBeCalled();
        $filterValidator->validateLocaleForAttribute('nutrition', null)->shouldBeCalled();

        $searchQueryBuilder->addFilter([
            'exists' => [
                'field' => 'values.nutrition-table.ecommerce.<all_locales>',
            ],
        ])->shouldBeCalled()->willReturn($searchQueryBuilder);

        $this->addAttributeFilter($nutrition, Operators::IS_NOT_EMPTY, null, null, 'ecommerce')->shouldReturn($this);
    }

    function it_adds_a_filter_with_not_empty_operator_and_localizable_attribute(
        ElasticsearchFilterValidator $filterValidator,
        SearchQueryBuilder $searchQueryBuilder,
        AttributeInterface $nutrition
    ) {
        $nutrition->getType()->willReturn(AttributeTypes::TABLE);
        $nutrition->getCode()->willReturn('nutrition');
        $nutrition->getBackendType()->willReturn('table');
        $nutrition->isScopable()->willReturn(false);
        $nutrition->isLocalizable()->willReturn(true);

        $filterValidator->validateChannelForAttribute('nutrition', null)->shouldBeCalled();
        $filterValidator->validateLocaleForAttribute('nutrition', 'en_US')->shouldBeCalled();

        $searchQueryBuilder->addFilter([
            'exists' => [
                'field' => 'values.nutrition-table.<all_channels>.en_US',
            ],
        ])->shouldBeCalled()->willReturn($searchQueryBuilder);

        $this->addAttributeFilter($nutrition, Operators::IS_NOT_EMPTY, null, 'en_US', null)->shouldReturn($this);
    }

    function it_adds_a_filter_with_not_empty_operator_with_no_locale_and_no_channel(
        ElasticsearchFilterValidator $filterValidator,
        SearchQueryBuilder $searchQueryBuilder,
        AttributeInterface $nutrition
    ) {
        $nutrition->getType()->willReturn(AttributeTypes::TABLE);
        $nutrition->getCode()->willReturn('nutrition');
        $nutrition->getBackendType()->willReturn('table');
        $nutrition->isScopable()->willReturn(true);
        $nutrition->isLocalizable()->willReturn(false);

        $filterValidator->validateChannelForAttribute('nutrition', null)->shouldBeCalled();
        $filterValidator->validateLocaleForAttribute('nutrition', null)->shouldBeCalled();

        $searchQueryBuilder->addFilter([
            'exists' => [
                'field' => 'values.nutrition-table.<all_channels>.<all_locales>',
            ],
        ])->shouldBeCalled()->willReturn($searchQueryBuilder);

        $this->addAttributeFilter($nutrition, Operators::IS_NOT_EMPTY, null, null, null)->shouldReturn($this);
    }
}
