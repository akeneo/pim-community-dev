<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Elasticsearch\Filter;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\AbstractFieldFilter;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QualityScoreFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    public function __construct()
    {
        $this->supportedFields = ['data_quality_insights_score'];
        $this->supportedOperators = ['IN'];
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $values, $locale = null, $channel = null, $options = [])
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        if (!is_array($values)) {
            throw InvalidPropertyTypeException::arrayExpected($field, static::class, $values);
        }

        $ratesEnrichmentField = sprintf('rates.enrichment.%s.%s', $channel, $locale);
        $ratesConsistencyField = sprintf('rates.consistency.%s.%s', $channel, $locale);
        $avgRatesConditions = implode(' || ', array_map(fn ($scoreValue) => sprintf("avgRates == %d", $scoreValue), $values));

        $clause = [
            'bool' => [
                'should' => [
                    [
                        'terms' => [sprintf('data_quality_insights.scores.%s.%s', $channel, $locale) => $values]
                    ],
                    [
                        'bool' => [
                            'must_not' => [
                                ['exists' => ['field' => 'data_quality_insights.scores']]
                            ],
                            'must' => [
                                [
                                    'script' => ['script' =>[
                                        'lang' => 'painless',
                                        'source' => "long avgRates = Math.round(((doc.containsKey('$ratesEnrichmentField') && doc['$ratesEnrichmentField'].size() > 0 ? Integer.parseInt(doc['$ratesEnrichmentField'].value) : 0) + (doc.containsKey('$ratesConsistencyField') && doc['$ratesConsistencyField'].size() > 0 ? Integer.parseInt(doc['$ratesConsistencyField'].value) : (doc.containsKey('$ratesEnrichmentField') && doc['$ratesEnrichmentField'].size() > 0 ? Integer.parseInt(doc['$ratesEnrichmentField'].value) : 0))) / 2); $avgRatesConditions"
                                    ]]
                                ]
                            ],
                        ]
                    ]
                ],
            ],
        ];

        $this->searchQueryBuilder->addFilter($clause);

        return $this;
    }
}
