<?php

namespace spec\Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field\ProductModel;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field\ProductModel\CompleteFilter;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Elasticsearch\SearchQueryBuilder;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Prophecy\Argument;

class CompleteFilterSpec extends ObjectBehavior
{
    function let(SearchQueryBuilder $sqb)
    {
        $this->beConstructedWith(['complete'], [
            'COMPLETE',
            'INCOMPLETE',
        ]);

        $this->setQueryBuilder($sqb);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CompleteFilter::class);
    }

    function it_is_a_fieldFilter()
    {
        $this->shouldImplement(FieldFilterInterface::class);
    }

    function it_has_operators()
    {
        $this->getOperators()->shouldReturn(
            [
                'COMPLETE',
                'INCOMPLETE',
            ]
        );
    }

    function it_supports_operators()
    {
        $this->supportsOperator('COMPLETE')->shouldReturn(true);
        $this->supportsOperator('INCOMPLETE')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_a_complete_filter($sqb)
    {
        $sqb->addFilter(
            [
                'query' => [
                    'constant_score' => [
                        'filter' => [
                            'term' => [
                                'at_least_complete.ecommerce.en_US' => 1,
                            ],
                        ],
                    ],
                ],
            ]
        )->shouldBeCalled();

        $this->addFieldFilter('completeness', Operators::AT_LEAST_COMPLETE, null, 'en_US', 'ecommerce', []);
    }

    function it_adds_a_incomplete_filter($sqb)
    {
        $sqb->addFilter(
            [
                'query' => [
                    'constant_score' => [
                        'filter' => [
                            'term' => [
                                'at_least_incomplete.ecommerce.en_US' => 1,
                            ],
                        ],
                    ],
                ],
            ]
        )->shouldBeCalled();

        $this->addFieldFilter('completeness', Operators::AT_LEAST_INCOMPLETE, null, 'en_US', 'ecommerce', []);
    }

    function it_filters_with_a_non_empty_locale()
    {
        $this->shouldThrow(InvalidPropertyException::class)->during(
            'addFieldFilter',
            ['completeness', Operators::AT_LEAST_COMPLETE, null, '', 'ecommerce']
        );
    }

    function it_filters_with_a_non_empty_channel()
    {
        $this->shouldThrow(InvalidPropertyException::class)->during(
            'addFieldFilter',
            ['completeness', Operators::AT_LEAST_COMPLETE, null, 'en_US', '']
        );
    }

    function it_throws_an_exception_if_operator_is_not_supported()
    {
        $this->shouldThrow(InvalidOperatorException::class)->during(
            'addFieldFilter',
            ['completeness', 'WrongOperator', null, 'ecommerce', 'en_US']
        );
    }
}
