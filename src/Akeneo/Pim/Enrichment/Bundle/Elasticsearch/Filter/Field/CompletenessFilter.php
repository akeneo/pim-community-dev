<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;

/**
 * Filter in/complete product model and product depending their completeness (variant product) or if it has at least
 * one in/complete variant product (product model).
 *
 * The supported operator are:
 *   - AT_LEAST_COMPLETE
 *   - AT_LEAST_INCOMPLETE
 *   - ALL_COMPLETE
 *   - ALL_INCOMPLETE
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    /**
     * @param array $supportedFields
     * @param array $supportedOperators
     */
    public function __construct(array $supportedFields = [], array $supportedOperators = [])
    {
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $channel = null, $options = [])
    {
        if (empty($locale) && empty($options['locales'])) {
            throw InvalidPropertyException::dataExpected('completeness', 'a valid locale', static::class);
        }

        if (empty($channel)) {
            throw InvalidPropertyException::dataExpected('completeness', 'a valid channel', static::class);
        }

        $locales = empty($locale) ? $options['locales'] : [$locale];

        // How the filter works
        // - At least one complete:   all_incomplete == false
        // - All complete:            all_complete == true
        // - At least one incomplete: all_complete == false
        // - All incomplete:          all_incomplete == true
        // - Equals, Not equal, lower than, and greater than are used to filter variant products on the completeness
        switch ($operator) {
            case Operators::AT_LEAST_COMPLETE:
                $shouldClauses = [];
                foreach ($locales as $locale) {
                    $completeness = sprintf('completeness.%s.%s', $channel, $locale);
                    $allIncomplete = sprintf('all_incomplete.%s.%s', $channel, $locale);
                    $shouldClauses[] = [
                        'bool' => [
                            'should' => [
                                ['term' => [$completeness => 100]],
                                ['term' => [$allIncomplete => 0]],
                            ],
                            'minimum_should_match' => 1,
                        ],
                    ];
                }
                $this->searchQueryBuilder->addFilter(['bool' => ['should' => $shouldClauses]]);
                break;

            case Operators::ALL_COMPLETE:
                $mustClauses = [];
                foreach ($locales as $locale) {
                    $completeness = sprintf('completeness.%s.%s', $channel, $locale);
                    $allComplete = sprintf('all_complete.%s.%s', $channel, $locale);
                    $mustClauses[] = [
                        'bool' => [
                            'should' => [
                                ['term' => [$completeness => 100]],
                                ['term' => [$allComplete => 1]],
                            ],
                            'minimum_should_match' => 1,
                        ],
                    ];
                }
                $this->searchQueryBuilder->addFilter(['bool' => ['must' => $mustClauses]]);
                break;

            case Operators::AT_LEAST_INCOMPLETE:
                $shouldClause = [];
                foreach ($locales as $locale) {
                    $completeness = sprintf('completeness.%s.%s', $channel, $locale);
                    $allComplete = sprintf('all_complete.%s.%s', $channel, $locale);
                    $shouldClause[] = [
                        'bool' => [
                            'should' => [
                                ['range' => [$completeness => ['lt' => 100]]],
                                ['term' => [$allComplete => 0]],
                            ],
                            'minimum_should_match' => 1,
                        ],
                    ];
                }
                $this->searchQueryBuilder->addFilter(['bool' => ['should' => $shouldClause]]);
                break;

            case Operators::ALL_INCOMPLETE:
                $mustClauses = [];
                foreach ($locales as $locale) {
                    $completeness = sprintf('completeness.%s.%s', $channel, $locale);
                    $allIncomplete = sprintf('all_incomplete.%s.%s', $channel, $locale);
                    $mustClauses[] = [
                        'bool' => [
                            'should' => [
                                ['range' => [$completeness => ['lt' => 100]]],
                                ['term' => [$allIncomplete => 1]],
                            ],
                            'minimum_should_match' => 1,
                        ],
                    ];
                }
                $this->searchQueryBuilder->addFilter(['bool' => ['must' => $mustClauses]]);
                break;

            case Operators::EQUALS:
                $shouldClauses = [];

                foreach ($locales as $locale) {
                    $field = sprintf('completeness.%s.%s', $channel, $locale);
                    $clause = [
                        'term' => [
                            $field => $value
                        ]
                    ];

                    $shouldClauses[] = $clause;
                }

                $this->searchQueryBuilder->addFilter(['bool' => ['should' => $shouldClauses]]);
                break;

            case Operators::LOWER_THAN:
                $shouldClauses = [];

                foreach ($locales as $locale) {
                    $field = sprintf('completeness.%s.%s', $channel, $locale);
                    $clause = [
                        'range' => [
                            $field => [
                                'lt' => $value
                            ]
                        ]
                    ];

                    $shouldClauses[] = $clause;
                }
                $this->searchQueryBuilder->addFilter(['bool' => ['should' => $shouldClauses]]);
                break;

            case Operators::GREATER_THAN:
                $shouldClauses = [];

                foreach ($locales as $locale) {
                    $field = sprintf('completeness.%s.%s', $channel, $locale);
                    $clause = [
                        'range' => [
                            $field => [
                                'gt' => $value
                            ]
                        ]
                    ];

                    $shouldClauses[] = $clause;
                }
                $this->searchQueryBuilder->addFilter(['bool' => ['should' => $shouldClauses]]);
                break;

            case Operators::NOT_EQUAL:
                $filterClauses = [];
                foreach ($locales as $locale) {
                    $field = sprintf('completeness.%s.%s', $channel, $locale);
                    $filterClauses[] = [
                        'term' => [
                            $field => $value,
                        ],
                    ];

                    $completenessExists = [
                        'exists' => [
                            'field' => $field,
                        ],
                    ];
                    $this->searchQueryBuilder->addFilter($completenessExists);
                }
                $mustNotClause = [
                    'bool' => [
                        'filter' => $filterClauses,
                    ],
                ];
                $this->searchQueryBuilder->addMustNot($mustNotClause);
                break;

            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }

        return $this;
    }
}
