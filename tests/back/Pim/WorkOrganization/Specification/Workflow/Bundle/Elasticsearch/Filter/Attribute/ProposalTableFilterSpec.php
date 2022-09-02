<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute\ProposalTableFilter;
use PhpSpec\ObjectBehavior;

class ProposalTableFilterSpec extends ObjectBehavior
{
    function let(SearchQueryBuilder $searchQueryBuilder)
    {
        $this->setQueryBuilder($searchQueryBuilder);
    }

    function it_is_a_table_attribute_filter()
    {
        $this->shouldHaveType(ProposalTableFilter::class);
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

    function it_adds_a_filter_with_not_empty_operator(
        SearchQueryBuilder $searchQueryBuilder,
        AttributeInterface $nutrition
    ) {
        $nutrition->getType()->willReturn(AttributeTypes::TABLE);
        $nutrition->getCode()->willReturn('nutrition');
        $nutrition->getBackendType()->willReturn('table');

        $searchQueryBuilder->addFilter([
            'exists' => [
                'field' => 'values.nutrition-table',
            ],
        ])->shouldBeCalled()->willReturn($searchQueryBuilder);

        $this->addAttributeFilter($nutrition, Operators::IS_NOT_EMPTY, null, 'en_US', 'ecommerce')->shouldReturn($this);
    }
}
