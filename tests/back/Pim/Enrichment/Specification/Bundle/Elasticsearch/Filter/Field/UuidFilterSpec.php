<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\UuidFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

class UuidFilterSpec extends ObjectBehavior
{
    function let(SearchQueryBuilder $queryBuilder)
    {
        $this->beConstructedWith('product_');
        $this->setQueryBuilder($queryBuilder);
    }

    function it_is_a_query_filter()
    {
        $this->shouldImplement(FieldFilterInterface::class);
        $this->shouldHaveType(UuidFilter::class);
    }

    function it_only_supports_in_list_and_not_in_list_operators()
    {
        $this->shouldThrow(InvalidOperatorException::class)->during(
            'addFieldFilter',
            [
                'uuid',
                '=',
                ['ca4787d5-36fd-4893-ba46-f4edd71b7186', 'dc832a6d-b2fb-4918-b169-eadb92242b85'],
            ]
        );
    }

    function it_throws_an_error_if_value_is_not_an_array()
    {
        $this->shouldThrow(
            InvalidPropertyTypeException::arrayExpected(
                'uuid',
                UuidFilter::class,
                'ca4787d5-36fd-4893-ba46-f4edd71b7186'
            )
        )->during(
            'addFieldFilter',
            [
                'uuid',
                'IN',
                'ca4787d5-36fd-4893-ba46-f4edd71b7186',
            ]
        );
    }

    function it_throws_an_error_if_value_is_not_an_array_of_strings()
    {
        $this->shouldThrow(
            InvalidPropertyTypeException::arrayOfStringsExpected(
                'uuid',
                UuidFilter::class,
                [123, false]
            )
        )->during(
            'addFieldFilter',
            [
                'uuid',
                'IN',
                [123, false],
            ]
        );
    }

    function it_adds_an_in_list_filter(SearchQueryBuilder $queryBuilder)
    {
        $queryBuilder->addFilter([
            'terms' => [
                'id' => [
                    'product_ca4787d5-36fd-4893-ba46-f4edd71b7186',
                    'product_dc832a6d-b2fb-4918-b169-eadb92242b85',
                ],
            ],
        ])->shouldBeCalled();
        $this->addFieldFilter(
            'uuid',
            'IN',
            ['ca4787d5-36fd-4893-ba46-f4edd71b7186', 'dc832a6d-b2fb-4918-b169-eadb92242b85']
        )->shouldReturn($this);
    }

    function it_adds_a_not_in_list_filter(SearchQueryBuilder $queryBuilder)
    {
        $queryBuilder->addMustNot([
            'terms' => [
                'id' => [
                    'product_ca4787d5-36fd-4893-ba46-f4edd71b7186',
                    'product_dc832a6d-b2fb-4918-b169-eadb92242b85',
                ],
            ],
        ])->shouldBeCalled();
        $this->addFieldFilter(
            'uuid',
            'NOT IN',
            ['ca4787d5-36fd-4893-ba46-f4edd71b7186', 'dc832a6d-b2fb-4918-b169-eadb92242b85']
        )->shouldReturn($this);
    }

    function it_is_case_insensitive(SearchQueryBuilder $queryBuilder)
    {
        $queryBuilder->addFilter([
            'terms' => [
                'id' => [
                    'product_ca4787d5-36fd-4893-ba46-f4edd71b7186',
                    'product_dc832a6d-b2fb-4918-b169-eadb92242b85',
                ],
            ],
        ])->shouldBeCalled();
        $this->addFieldFilter(
            'uuid',
            'IN',
            ['CA4787D5-36FD-4893-BA46-F4EDD71B7186', 'DC832A6D-B2FB-4918-B169-EADB92242B85']
        )->shouldReturn($this);
    }
}
