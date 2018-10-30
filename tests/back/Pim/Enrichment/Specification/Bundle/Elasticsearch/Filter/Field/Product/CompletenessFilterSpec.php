<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\Product;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\Product\CompletenessFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;

class CompletenessFilterSpec extends ObjectBehavior
{
    function let(CachedObjectRepositoryInterface $channelRepository, SearchQueryBuilder $sqb)
    {
        $operators = [
            'IS_EMPTY',
            'NOT EQUALS ON AT LEAST ONE LOCALE',
            'EQUALS ON AT LEAST ONE LOCALE',
            'GREATER THAN ON AT LEAST ONE LOCALE',
            'GREATER OR EQUALS THAN ON AT LEAST ONE LOCALE',
            'LOWER OR EQUALS THAN ON AT LEAST ONE LOCALE',
            'LOWER THAN ON AT LEAST ONE LOCALE',
            'GREATER THAN ON ALL LOCALES',
            'GREATER OR EQUALS THAN ON ALL LOCALES',
            'LOWER OR EQUALS THAN ON ALL LOCALES',
            'LOWER THAN ON ALL LOCALES',
            '!=',
            '=',
            '>',
            '>=',
            '<=',
            '<'
        ];

        $this->beConstructedWith($channelRepository, ['completeness'], $operators);
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

    function it_supports_operators()
    {
        $this->getOperators()->shouldReturn(
            [
                'IS_EMPTY',
                'NOT EQUALS ON AT LEAST ONE LOCALE',
                'EQUALS ON AT LEAST ONE LOCALE',
                'GREATER THAN ON AT LEAST ONE LOCALE',
                'GREATER OR EQUALS THAN ON AT LEAST ONE LOCALE',
                'LOWER OR EQUALS THAN ON AT LEAST ONE LOCALE',
                'LOWER THAN ON AT LEAST ONE LOCALE',
                'GREATER THAN ON ALL LOCALES',
                'GREATER OR EQUALS THAN ON ALL LOCALES',
                'LOWER OR EQUALS THAN ON ALL LOCALES',
                'LOWER THAN ON ALL LOCALES',
                '!=',
                '=',
                '>',
                '>=',
                '<=',
                '<'
            ]
        );
        $this->supportsOperator('NOT EQUALS ON AT LEAST ONE LOCALE')->shouldReturn(true);
        $this->supportsOperator('FAKE')->shouldReturn(false);
    }

    function it_throws_an_exception_with_all_locales_filters_when_there_is_no_provided_locales(
        $channelRepository,
        ChannelInterface $ecommerce
    ) {
        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($ecommerce);

        $this->shouldThrow(InvalidPropertyTypeException::class)->during(
            'addFieldFilter',
            ['completeness', Operators::LOWER_THAN_ON_ALL_LOCALES, 100, null, 'ecommerce', []]
        );
    }

    function it_throws_an_exception_with_all_locales_filters_when_provided_locales_is_not_an_array(
        $channelRepository,
        ChannelInterface $ecommerce
    ) {
        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($ecommerce);

        $this->shouldThrow(InvalidPropertyTypeException::class)->during(
            'addFieldFilter',
            ['completeness', Operators::LOWER_THAN_ON_ALL_LOCALES, 100, null, 'ecommerce', ['locales' => 'WRONG']]
        );
    }

    function it_throws_an_exception_when_there_is_no_channel()
    {
        $this->shouldThrow(InvalidPropertyException::class)->during(
            'addFieldFilter',
            ['completeness', Operators::LOWER_THAN_ON_ALL_LOCALES, 100, null, null, ['locales' => ['fr_FR']]]
        );
    }

    function it_throws_an_exception_when_the_channel_is_unknown_and_when_there_is_no_locale($channelRepository)
    {
        $channelRepository->findOneByIdentifier('UNKNOWN')->willReturn(null);

        $this->shouldThrow(ObjectNotFoundException::class)->during(
            'addFieldFilter',
            [
                'completeness',
                Operators::LOWER_THAN_ON_AT_LEAST_ONE_LOCALE,
                100,
                null,
                'UNKNOWN',
                ['locales' => ['fr_FR']]
            ]
        );
    }

    function it_throws_an_exception_when_the_data_is_wrong($channelRepository, ChannelInterface $ecommerce)
    {
        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($ecommerce);

        $this->shouldThrow(InvalidPropertyTypeException::class)->during(
            'addFieldFilter',
            [
                'completeness',
                Operators::LOWER_THAN_ON_ALL_LOCALES,
                ['WRONG DATA'],
                null,
                'ecommerce',
                ['locales' => ['fr_FR']]
            ]
        );
    }

    function it_adds_a_filter_on_one_locale_with_a_regular_filter($sqb)
    {
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['term' => ['completeness.ecommerce.en_US' => 56]]
                    ]
                ]
            ]
        )->shouldBeCalled();

        $this->addFieldFilter('completeness', Operators::EQUALS, 56, 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_on_all_locales_of_a_channel_with_a_regular_filter(
        $sqb,
        $channelRepository,
        ChannelInterface $ecommerce
    ) {
        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($ecommerce);
        $ecommerce->getLocaleCodes()->willReturn(['en_US', 'fr_FR']);

        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['term' => ['completeness.ecommerce.en_US' => 56]],
                        ['term' => ['completeness.ecommerce.fr_FR' => 56]]
                    ]
                ]
            ]
        );

        $this->addFieldFilter('completeness', Operators::EQUALS, 56, null, 'ecommerce', []);
    }

    function it_adds_a_filter_on_one_locale_with_an_ALL_LOCALES_filter($sqb)
    {
        $sqb->addFilter(
            [
                'range' => [
                    'completeness.ecommerce.fr_FR' => [
                        'gt' => 56
                    ],
                ],
            ]
        )->shouldBeCalled();

        $this->addFieldFilter(
            'completeness',
            Operators::GREATER_THAN_ON_ALL_LOCALES,
            56,
            null,
            'ecommerce',
            ['locales' => ['fr_FR']]
        );
    }

    function it_adds_a_filter_on_several_locales_with_an_ALL_LOCALES_filter($sqb)
    {
        $sqb->addFilter(
            [
                'range' => [
                    'completeness.ecommerce.fr_FR' => [
                        'gt' => 56
                    ],
                ],
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'range' => [
                    'completeness.ecommerce.it_IT' => [
                        'gt' => 56
                    ],
                ],
            ]
        )->shouldBeCalled();

        $this->addFieldFilter(
            'completeness',
            Operators::GREATER_THAN_ON_ALL_LOCALES,
            56,
            null,
            'ecommerce',
            ['locales' => ['fr_FR', 'it_IT']]
        );
    }

    function it_adds_a_filter_on_EQUALS_ON_AT_LEAST_ONE_LOCALE_operator($sqb)
    {
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['term' => ['completeness.ecommerce.en_US' => 56]]
                    ]
                ]
            ]
        )->shouldBeCalled();

        $this->addFieldFilter('completeness', Operators::EQUALS_ON_AT_LEAST_ONE_LOCALE, 56, 'en_US', 'ecommerce', []);
        $this->addFieldFilter('completeness', Operators::EQUALS, 56, 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_on_NOT_EQUALS_ON_AT_LEAST_ONE_LOCALE_operator_on_one_locale($sqb)
    {
        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'completeness.ecommerce.en_US',
                ],
            ]
        )->shouldBeCalled();

        $sqb->addMustNot(
            [
                'bool' => [
                    'filter' => [
                        ['term' => ['completeness.ecommerce.en_US' => 56]],
                    ],
                ],
            ]
        )->shouldBeCalled();

        $this->addFieldFilter(
            'completeness',
            Operators::NOT_EQUALS_ON_AT_LEAST_ONE_LOCALE,
            56,
            'en_US',
            'ecommerce',
            []
        );
        $this->addFieldFilter('completeness', Operators::NOT_EQUAL, 56, 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_on_NOT_EQUALS_ON_AT_LEAST_ONE_LOCALE_operator_on_several_locales(
        $sqb,
        $channelRepository,
        ChannelInterface $ecommerce
    ) {
        $channelRepository->findOneByIdentifier('ecommerce')->willReturn($ecommerce);
        $ecommerce->getLocaleCodes()->willReturn(['en_US', 'fr_FR']);

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'completeness.ecommerce.en_US',
                ],
            ]
        )->shouldBeCalled();

        $sqb->addFilter(
            [
                'exists' => [
                    'field' => 'completeness.ecommerce.fr_FR',
                ],
            ]
        )->shouldBeCalled();

        $sqb->addMustNot(
            [
                'bool' => [
                    'filter' => [
                        ['term' => ['completeness.ecommerce.en_US' => 56]],
                        ['term' => ['completeness.ecommerce.fr_FR' => 56]],
                    ],
                ],
            ]
        )->shouldBeCalled();

        $this->addFieldFilter('completeness', Operators::NOT_EQUALS_ON_AT_LEAST_ONE_LOCALE, 56, null, 'ecommerce', []);
        $this->addFieldFilter('completeness', Operators::NOT_EQUAL, 56, null, 'ecommerce', []);
    }

    function it_adds_a_filter_on_LOWER_THAN_ON_AT_LEAST_ONE_LOCALE_operator($sqb)
    {
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['range' => ['completeness.ecommerce.en_US' => ['lt' => 56]]]
                    ]
                ]
            ]
        )->shouldBeCalled();

        $this->addFieldFilter(
            'completeness',
            Operators::LOWER_THAN_ON_AT_LEAST_ONE_LOCALE,
            56,
            'en_US',
            'ecommerce',
            []
        );
        $this->addFieldFilter('completeness', Operators::LOWER_THAN, 56, 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_on_LOWER_THAN_ON_ALL_LOCALES_operator($sqb)
    {
        $sqb->addFilter(
            [
                'range' => [
                    'completeness.ecommerce.en_US' => ['lt' => 56],
                ],
            ]
        )->shouldBeCalled();

        $this->addFieldFilter(
            'completeness',
            Operators::LOWER_THAN_ON_ALL_LOCALES,
            56,
            null,
            'ecommerce',
            ['locales' => ['en_US']]
        );
    }

    function it_adds_a_filter_on_GREATER_THAN_ON_AT_LEAST_ONE_LOCALE_operator($sqb)
    {
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['range' => ['completeness.ecommerce.en_US' => ['gt' => 56]]]
                    ]
                ]
            ]
        )->shouldBeCalled();

        $this->addFieldFilter(
            'completeness',
            Operators::GREATER_THAN_ON_AT_LEAST_ONE_LOCALE,
            56,
            'en_US',
            'ecommerce',
            []
        );
        $this->addFieldFilter('completeness', Operators::GREATER_THAN, 56, 'en_US', 'ecommerce', []);
    }

    function it_adds_a_filter_on_GREATER_THAN_ON_ALL_LOCALES_operator($sqb)
    {
        $sqb->addFilter(
            [
                'range' => [
                    'completeness.ecommerce.en_US' => ['gt' => 56],
                ],
            ]
        )->shouldBeCalled();

        $this->addFieldFilter(
            'completeness',
            Operators::GREATER_THAN_ON_ALL_LOCALES,
            56,
            null,
            'ecommerce',
            ['locales' => ['en_US']]
        );
    }

    function it_adds_a_filter_on_LOWER_OR_EQUALS_THAN_ON_AT_LEAST_ONE_LOCALE_operator($sqb)
    {
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['range' => ['completeness.ecommerce.en_US' => ['lte' => 56]]]
                    ]
                ]
            ]
        )->shouldBeCalled();

        $this->addFieldFilter(
            'completeness',
            Operators::LOWER_OR_EQUALS_THAN_ON_AT_LEAST_ONE_LOCALE,
            56,
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_on_LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES_operator($sqb)
    {
        $sqb->addFilter(
            [
                'range' => [
                    'completeness.ecommerce.en_US' => ['lte' => 56],
                ],
            ]
        )->shouldBeCalled();

        $this->addFieldFilter(
            'completeness',
            Operators::LOWER_OR_EQUALS_THAN_ON_ALL_LOCALES,
            56,
            null,
            'ecommerce',
            ['locales' => ['en_US']]
        );
    }

    function it_adds_a_filter_on_GREATER_OR_EQUALS_THAN_ON_AT_LEAST_ONE_LOCALE_operator($sqb)
    {
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['range' => ['completeness.ecommerce.en_US' => ['gte' => 56]]]
                    ]
                ]
            ]
        )->shouldBeCalled();

        $this->addFieldFilter(
            'completeness',
            Operators::GREATER_OR_EQUALS_THAN_ON_AT_LEAST_ONE_LOCALE,
            56,
            'en_US',
            'ecommerce',
            []
        );
    }

    function it_adds_a_filter_on_GREATER_OR_EQUALS_THAN_ON_AT_LEAST_ONE_LOCALE_operator_with_locales($sqb)
    {
        $sqb->addFilter(
            [
                'bool' => [
                    'should' => [
                        ['range' => ['completeness.ecommerce.en_US' => ['gte' => 56]]]
                    ]
                ]
            ]
        )->shouldBeCalled();

        $this->addFieldFilter(
            'completeness',
            Operators::GREATER_OR_EQUALS_THAN_ON_AT_LEAST_ONE_LOCALE,
            56,
            null,
            'ecommerce',
            ['locales' => ['en_US']]
        );
    }

    function it_adds_a_filter_on_GREATER_OR_EQUALS_THAN_ON_ALL_LOCALES_operator($sqb)
    {
        $sqb->addFilter(
            [
                'range' => [
                    'completeness.ecommerce.en_US' => ['gte' => 56],
                ],
            ]
        )->shouldBeCalled();

        $this->addFieldFilter(
            'completeness',
            Operators::GREATER_OR_EQUALS_THAN_ON_ALL_LOCALES,
            56,
            null,
            'ecommerce',
            ['locales' => ['en_US']]
        );
    }

    function it_adds_a_filter_on_IS_EMPTY_operator($sqb)
    {
        $sqb->addMustNot(
            [
                'exists' => [
                    'field' => 'completeness',
                ],
            ]
        )->shouldBeCalled();

        $this->addFieldFilter('completeness', Operators::IS_EMPTY, null, null, null, []);
        $this->addFieldFilter('completeness', Operators::IS_EMPTY, 'IGNORED', null, null, []);
        $this->addFieldFilter('completeness', Operators::IS_EMPTY, null, 'en_US', null, []);
        $this->addFieldFilter('completeness', Operators::IS_EMPTY, null, null, 'ecommerce', []);
    }
}
