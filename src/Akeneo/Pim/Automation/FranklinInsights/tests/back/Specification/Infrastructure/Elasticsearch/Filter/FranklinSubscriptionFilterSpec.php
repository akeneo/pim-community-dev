<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Elasticsearch\Filter;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Elasticsearch\Filter\FranklinSubscriptionFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\AbstractFieldFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class FranklinSubscriptionFilterSpec extends ObjectBehavior
{
    public function it_is_a_field_filter(): void
    {
        $this->shouldImplement(FieldFilterInterface::class);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(AbstractFieldFilter::class);
        $this->shouldHaveType(FranklinSubscriptionFilter::class);
    }

    public function it_adds_a_field_filter_to_search_products_that_are_subscribed_to_franklin(
        SearchQueryBuilder $searchQueryBuilder
    ): void {
        $this->setQueryBuilder($searchQueryBuilder);
        $searchQueryBuilder->addFilter([
            'term' => [
                'franklin_subscription' => true,
            ],
        ])->shouldBeCalled();

        $this->addFieldFilter('franklin_subscription', '=', true);
    }

    public function it_adds_a_field_filter_to_search_products_that_are_not_subscribed_to_franklin(
        SearchQueryBuilder $searchQueryBuilder
    ): void {
        $this->setQueryBuilder($searchQueryBuilder);
        $searchQueryBuilder->addShould([
            [
                'term' => [
                    'franklin_subscription' => false,
                ],
            ],
            [
                'bool' => [
                    'must_not' => [
                        'exists' => [
                            'field' => 'franklin_subscription',
                        ],
                    ],
                ],
            ],
        ])->shouldBeCalled();

        $this->addFieldFilter('franklin_subscription', '=', false);
    }

    public function it_throws_an_exception_if_the_value_is_not_a_boolean(
        SearchQueryBuilder $searchQueryBuilder
    ): void {
        $this->setQueryBuilder($searchQueryBuilder);

        $this->shouldThrow(InvalidPropertyTypeException::class)->during('addFieldFilter', [
            'franklin_subscription', '=', null,
        ]);

        $this->shouldThrow(InvalidPropertyTypeException::class)->during('addFieldFilter', [
            'franklin_subscription', '=', 1,
        ]);

        $this->shouldThrow(InvalidPropertyTypeException::class)->during('addFieldFilter', [
            'franklin_subscription', '=', 'false',
        ]);
    }
}
