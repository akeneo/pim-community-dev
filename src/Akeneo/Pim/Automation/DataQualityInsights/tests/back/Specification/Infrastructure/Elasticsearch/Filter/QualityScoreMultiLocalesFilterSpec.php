<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\Filter;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\Filter\QualityScoreMultiLocalesFilter;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class QualityScoreMultiLocalesFilterSpec extends ObjectBehavior
{
    public function let(SearchQueryBuilder $queryBuilder)
    {
        $this->setQueryBuilder($queryBuilder);
    }

    public function it_adds_filter_on_quality_score_for_at_least_one_locale($queryBuilder)
    {
        $queryBuilder->addFilter(
            [
                'bool' => [
                    'should' => [
                        [
                            'terms' => [
                               'data_quality_insights.scores.ecommerce.en_US' => [1, 2]
                            ],
                        ],
                        [
                            'terms' => [
                               'data_quality_insights.scores.ecommerce.fr_FR' => [1, 2]
                            ],
                        ]
                    ]
                ]

            ]
        )->shouldBeCalled();

        $this->addFieldFilter(
            QualityScoreMultiLocalesFilter::FIELD,
            QualityScoreMultiLocalesFilter::OPERATOR_IN_AT_LEAST_ONE_LOCALE,
            [1, 2],
            null,
            'ecommerce',
            [
                'locales' => ['en_US', 'fr_FR']
            ]
        );
    }

    public function it_adds_filter_on_quality_score_for_all_locales($queryBuilder)
    {
        $queryBuilder->addFilter(
            [
                'bool' => [
                    'must' => [
                        [
                            'terms' => [
                               'data_quality_insights.scores.ecommerce.en_US' => [1, 2]
                            ],
                        ],
                        [
                            'terms' => [
                               'data_quality_insights.scores.ecommerce.fr_FR' => [1, 2]
                            ],
                        ]
                    ]
                ]

            ]
        )->shouldBeCalled();

        $this->addFieldFilter(
            QualityScoreMultiLocalesFilter::FIELD,
            QualityScoreMultiLocalesFilter::OPERATOR_IN_ALL_LOCALES,
            [1, 2],
            null,
            'ecommerce',
            ['locales' => ['en_US', 'fr_FR']]
        );
    }

    public function it_throws_an_exception_if_the_values_are_not_an_array()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)->during('addFieldFilter', [
            QualityScoreMultiLocalesFilter::FIELD,
            QualityScoreMultiLocalesFilter::OPERATOR_IN_ALL_LOCALES,
            2,
            null,
            'ecommerce',
            ['locales' => ['en_US', 'fr_FR']]
        ]);
    }

    public function it_throws_an_exception_if_there_is_no_channel()
    {
        $this->shouldThrow(InvalidPropertyException::class)->during('addFieldFilter', [
            QualityScoreMultiLocalesFilter::FIELD,
            QualityScoreMultiLocalesFilter::OPERATOR_IN_ALL_LOCALES,
            [1, 3],
            null,
            null,
            ['locales' => ['en_US', 'fr_FR']]
        ]);
    }

    public function it_throws_an_exception_if_there_is_no_locale()
    {
        $this->shouldThrow(InvalidPropertyException::class)->during('addFieldFilter', [
            QualityScoreMultiLocalesFilter::FIELD,
            QualityScoreMultiLocalesFilter::OPERATOR_IN_ALL_LOCALES,
            [1, 2],
            null,
            'ecommerce',
            ['locales' => []]
        ]);

        $this->shouldThrow(InvalidPropertyTypeException::class)->during('addFieldFilter', [
            QualityScoreMultiLocalesFilter::FIELD,
            QualityScoreMultiLocalesFilter::OPERATOR_IN_ALL_LOCALES,
            [1, 2],
            null,
            'ecommerce',
            ['locales' => 'en_US']
        ]);

        $this->shouldThrow(InvalidPropertyTypeException::class)->during('addFieldFilter', [
            QualityScoreMultiLocalesFilter::FIELD,
            QualityScoreMultiLocalesFilter::OPERATOR_IN_ALL_LOCALES,
            [1, 2],
            null,
            'ecommerce',
            []
        ]);
    }
}
