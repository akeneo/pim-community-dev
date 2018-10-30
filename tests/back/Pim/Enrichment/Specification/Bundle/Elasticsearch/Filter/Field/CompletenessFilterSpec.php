<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\CompletenessFilter;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Prophecy\Argument;

class CompletenessFilterSpec extends ObjectBehavior
{
    function let(SearchQueryBuilder $sqb)
    {
        $this->beConstructedWith(['completeness'], [
            'ALL COMPLETE',
            'ALL INCOMPLETE',
        ]);

        $this->setQueryBuilder($sqb);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CompletenessFilter::class);
    }

    function it_is_a_fieldFilter()
    {
        $this->shouldImplement(FieldFilterInterface::class);
    }

    function it_has_operators()
    {
        $this->getOperators()->shouldReturn(
            [
                'ALL COMPLETE',
                'ALL INCOMPLETE',
            ]
        );
    }

    function it_supports_operators()
    {
        $this->supportsOperator('ALL COMPLETE')->shouldReturn(true);
        $this->supportsOperator('ALL INCOMPLETE')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_adds_an_at_least_complete_filter($sqb)
    {
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [[
                        'bool' => [
                            'should' => [
                                ['term' => ['completeness.ecommerce.en_US' => 100]],
                                ['term' => ['all_incomplete.ecommerce.en_US' => 0]],
                            ],
                            'minimum_should_match' => 1,
                        ],
                    ]],
                ],
            ]
        )->shouldBeCalled();

        $this->addFieldFilter('completeness', Operators::AT_LEAST_COMPLETE, null, 'en_US', 'ecommerce', [])
            ->shouldReturn($this);
    }

    function it_adds_an_at_least_incomplete_filter($sqb)
    {
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [[
                        'bool' => [
                            'should' => [
                                ['range' => ['completeness.ecommerce.fr_FR' => ['lt' => 100]]],
                                ['term' => ['all_complete.ecommerce.fr_FR' => 0]],
                            ],
                            'minimum_should_match' => 1,
                        ],
                    ]],
                ],
            ]
        )->shouldBeCalled();

        $this->addFieldFilter('completeness', Operators::AT_LEAST_INCOMPLETE, null, 'fr_FR', 'ecommerce', [])
            ->shouldReturn($this);
    }

    function it_adds_an_all_incomplete_filter($sqb)
    {
        $sqb->addFilter(
            [
                'bool' => [
                    'must' => [[
                        'bool' => [
                            'should' => [
                                ['range' => ['completeness.ecommerce.fr_FR' => ['lt' => 100]]],
                                ['term' => ['all_incomplete.ecommerce.fr_FR' => 1]],
                            ],
                            'minimum_should_match' => 1,
                        ],
                    ]],
                ],
            ]
        )->shouldBeCalled();

        $this->addFieldFilter('completeness', Operators::ALL_INCOMPLETE, null, 'fr_FR', 'ecommerce', [])
            ->shouldReturn($this);
    }

    function it_adds_an_all_complete_filter($sqb)
    {
        $sqb->addFilter(
            [
                'bool' => [
                    'must' => [[
                        'bool' => [
                            'should' => [
                                ['term' => ['completeness.ecommerce.en_US' => 100]],
                                ['term' => ['all_complete.ecommerce.en_US' => 1]],
                            ],
                            'minimum_should_match' => 1,
                        ],
                    ]],
                ],
            ]
        )->shouldBeCalled();

        $this->addFieldFilter('completeness', Operators::ALL_COMPLETE, null, 'en_US', 'ecommerce', [])
            ->shouldReturn($this);
    }

    function it_adds_an_equal_filter($sqb)
    {
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['term' => ['completeness.ecommerce.en_US' => 100]],
                    ],
                ],
            ]
        )->shouldBeCalled();

        $this->addFieldFilter('completeness', Operators::EQUALS, 100, 'en_US', 'ecommerce', [])
            ->shouldReturn($this);
    }

    function it_adds_a_not_equal_filter($sqb)
    {
        $sqb
            ->addFilter(
                [
                    'exists' => [
                        'field' => 'completeness.ecommerce.en_US'
                    ]
                ]
            )
            ->shouldBeCalled();

        $sqb
            ->addMustNot(
                [
                    'bool' => [
                        'filter' => [
                            ['term' => ["completeness.ecommerce.en_US" => 100]],
                        ],
                    ]
                ]
            )
            ->shouldBeCalled();

        $this->addFieldFilter('completeness', Operators::NOT_EQUAL, 100, 'en_US', 'ecommerce', [])
            ->shouldReturn($this);
    }

    function it_adds_a_lower_than_filter($sqb)
    {
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['range' => ['completeness.ecommerce.en_US' => ['lt' => 100]]]
                    ]
                ]
            ]
        )->shouldBeCalled();

        $this->addFieldFilter('completeness', Operators::LOWER_THAN, 100, 'en_US', 'ecommerce', [])
            ->shouldReturn($this);
    }

    function it_adds_a_greater_than_filter($sqb)
    {
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['range' => ['completeness.ecommerce.en_US' => ['gt' => 100]]]
                    ]
                ]
            ]
        )->shouldBeCalled();

        $this->addFieldFilter('completeness', Operators::GREATER_THAN, 100, 'en_US', 'ecommerce', [])
            ->shouldReturn($this);
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
