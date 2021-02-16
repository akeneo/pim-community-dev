<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\Filter;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class QualityScoreFilterSpec extends ObjectBehavior
{
    public function let(SearchQueryBuilder $queryBuilder)
    {
        $this->setQueryBuilder($queryBuilder);
    }

    public function it_adds_filter_on_quality_score_with_letter_values($queryBuilder)
    {
        $queryBuilder->addFilter(
            [
                'terms' => [
                   'data_quality_insights.scores.ecommerce.en_US' => [1, 2]
                ],
            ]
        )->shouldBeCalled();

        $this->addFieldFilter('quality_score', Operators::IN_LIST, ['A', 'B'], 'en_US', 'ecommerce', []);
    }

    public function it_adds_filter_on_quality_score_with_integer_values($queryBuilder)
    {
        $queryBuilder->addFilter(
            [
                'terms' => [
                   'data_quality_insights.scores.ecommerce.en_US' => [1, 3]
                ],
            ]
        )->shouldBeCalled();

        $this->addFieldFilter('data_quality_insights_score', Operators::IN_LIST, [1, 3], 'en_US', 'ecommerce', []);
    }

    public function it_throws_an_exception_if_the_values_are_not_an_array()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)->during('addFieldFilter', [
            'data_quality_insights_score',
            Operators::IN_LIST,
            2,
            'en_US',
            'ecommerce',
            []
        ]);
    }

    public function it_throws_an_exception_if_there_is_no_channel()
    {
        $this->shouldThrow(InvalidPropertyException::class)->during('addFieldFilter', [
            'quality_score',
            Operators::IN_LIST,
            ['A', 'B'],
            null,
            'ecommerce',
            []
        ]);
    }

    public function it_throws_an_exception_if_there_is_no_locale()
    {
        $this->shouldThrow(InvalidPropertyException::class)->during('addFieldFilter', [
            'quality_score',
            Operators::IN_LIST,
            ['A', 'B'],
            'en_US',
            null,
            []
        ]);
    }

    public function it_throws_an_exception_if_a_value_is_invalid()
    {
        $this->shouldThrow(InvalidPropertyException::class)->during('addFieldFilter', [
            'quality_score',
            Operators::IN_LIST,
            ['A', 'Z', 'B'],
            'en_US',
            'ecommerce',
            []
        ]);
    }
}
