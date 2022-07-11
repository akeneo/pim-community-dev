<?php

declare(strict_types=1);

namespace Akeneo\Pim\TableAttribute\Infrastructure\Value\Filter;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ColumnDefinition;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\MeasurementColumn;
use Akeneo\Pim\TableAttribute\Domain\Value\Measurement\MeasurementException;
use Akeneo\Pim\TableAttribute\Infrastructure\AntiCorruptionLayer\AclMeasureConverter;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ColumnFilter implements ColumnTypeFilter
{
    public function __construct(
        private string $supportedColumnType,
        private array $supportedOperators,
        private ?AclMeasureConverter $measureConverter = null
    ) {
    }

    public function supportedColumnType(): string
    {
        return $this->supportedColumnType;
    }

    public function supportsOperator(string $operator): bool
    {
        return \in_array($operator, $this->supportedOperators);
    }

    public function addFilter(
        SearchQueryBuilder $searchQueryBuilder,
        string $attributeCode,
        string $operator,
        ColumnDefinition $column,
        bool $isFirstColumn,
        ?string $rowCode,
        ?string $locale,
        ?string $channel,
        $value
    ): void {
        $attributePath = \sprintf('table_values.%s', $attributeCode);

        if ($column instanceof MeasurementColumn && !\in_array(
            $operator,
            [Operators::IS_EMPTY, Operators::IS_NOT_EMPTY],
            true
        )) {
            $value = $this->convertMeasurementValue($attributeCode, $column, $value);
        }

        switch ($operator) {
            case Operators::IS_NOT_EMPTY:
                $clause = $this->getColumnRowChannelAndLocaleFilters($attributeCode, $column, $rowCode, $locale, $channel);
                $clause['nested']['query']['bool']['filter'][] = [
                    'exists' => [
                        'field' => \sprintf('%s.value-%s', $attributePath, $column->dataType()->asString()),
                    ],
                ];
                $searchQueryBuilder->addFilter($clause);
                break;
            case Operators::IS_EMPTY:
                // We don't call the same method as the other operators because the empty operator should not
                // always filter on the column and the row. We add them only when needed.
                $filterClause = $this->getChannelAndLocaleFilters($attributeCode, $locale, $channel);
                $filterClause['nested']['query']['bool']['filter'][] = [
                    'exists' => [
                        'field' => \sprintf('%s.row', $attributePath),
                    ],
                ];
                if (null !== $rowCode && !$isFirstColumn) {
                    // Functional choice: when we want products that have a particular cell empty, we assume
                    // products have at least the row. In other words, products that don't have the row will not
                    //  appear in the results.
                    $filterClause['nested']['query']['bool']['filter'][] = [
                        'term' => [
                            \sprintf('%s.row', $attributePath) => $rowCode,
                        ],
                    ];
                }
            $searchQueryBuilder->addFilter($filterClause);

            $clause = $this->getChannelAndLocaleFilters($attributeCode, $locale, $channel);
            $clause['nested']['query']['bool']['filter'][] = [
                'term' => [
                    \sprintf('%s.column', $attributePath) => $column->code()->asString(),
                ],
            ];
            if (null === $rowCode && !$isFirstColumn) {
                // Check we don't have the is_column_complete equals true. Meaning either there is no value in the column
                // or one or several values are missing.
                $clause['nested']['query']['bool']['filter'][] = [
                    'term' => [
                        \sprintf('%s.is_column_complete', $attributePath) => true,
                    ],
                ];
                $searchQueryBuilder->addMustNot($clause);
            } elseif (null === $rowCode) {
                // Weird case, as the first column is always complete.
                // We just have to check that the table is not empty.
                $clause['nested']['query']['bool']['filter'][] = [
                    'exists' => [
                        'field' => \sprintf('%s.row', $attributePath),
                    ],
                ];
                $searchQueryBuilder->addMustNot($clause);
            } else {
                // We have a row code and a column code. We have to check that the cell defined by the column and the row is empty.
                $clause['nested']['query']['bool']['filter'][] = [
                    'term' => [
                        \sprintf('%s.row', $attributePath) => $rowCode,
                    ],
                ];
                $searchQueryBuilder->addMustNot($clause);
            }
            break;
            case Operators::EQUALS:
                $this->assertValueIsScalar($attributeCode, $value);
                $clause = $this->getColumnRowChannelAndLocaleFilters($attributeCode, $column, $rowCode, $locale, $channel);
                $clause['nested']['query']['bool']['filter'][] = [
                    'term' => [
                        \sprintf('%s.value-%s', $attributePath, $column->dataType()->asString()) => $value,
                    ],
                ];
                $searchQueryBuilder->addFilter($clause);
                break;
            case Operators::CONTAINS:
                $this->assertValueIsString($attributeCode, $value);
                $clause = $this->getColumnRowChannelAndLocaleFilters($attributeCode, $column, $rowCode, $locale, $channel);
                $clause['nested']['query']['bool']['filter'][] = [
                    'wildcard' => [
                        \sprintf('%s.value-%s', $attributePath, $column->dataType()->asString()) => [
                            'value' => \sprintf('*%s*', $value),
                        ],
                    ],
                ];
                $searchQueryBuilder->addFilter($clause);
                break;
            case Operators::STARTS_WITH:
                $this->assertValueIsString($attributeCode, $value);
                $clause = $this->getColumnRowChannelAndLocaleFilters($attributeCode, $column, $rowCode, $locale, $channel);
                $clause['nested']['query']['bool']['filter'][] = [
                    'wildcard' => [
                        \sprintf('%s.value-%s', $attributePath, $column->dataType()->asString()) => [
                            'value' => \sprintf('%s*', $value),
                        ],
                    ],
                ];
                $searchQueryBuilder->addFilter($clause);
                break;
            case Operators::ENDS_WITH:
                $this->assertValueIsString($attributeCode, $value);
                $clause = $this->getColumnRowChannelAndLocaleFilters($attributeCode, $column, $rowCode, $locale, $channel);
                $clause['nested']['query']['bool']['filter'][] = [
                    'wildcard' => [
                        \sprintf('%s.value-%s', $attributePath, $column->dataType()->asString()) => [
                            'value' => \sprintf('*%s', $value),
                        ],
                    ],
                ];
                $searchQueryBuilder->addFilter($clause);
                break;
            case Operators::GREATER_THAN:
                $this->assertValueIsNumeric($attributeCode, $value);
                $clause = $this->getColumnRowChannelAndLocaleFilters($attributeCode, $column, $rowCode, $locale, $channel);
                $clause['nested']['query']['bool']['filter'][] = [
                    'range' => [
                        \sprintf('%s.value-%s', $attributePath, $column->dataType()->asString()) => [
                            'gt' => $value,
                        ],
                    ],
                ];
                $searchQueryBuilder->addFilter($clause);
                break;
            case Operators::GREATER_OR_EQUAL_THAN:
                $this->assertValueIsNumeric($attributeCode, $value);
                $clause = $this->getColumnRowChannelAndLocaleFilters($attributeCode, $column, $rowCode, $locale, $channel);
                $clause['nested']['query']['bool']['filter'][] = [
                    'range' => [
                        \sprintf('%s.value-%s', $attributePath, $column->dataType()->asString()) => [
                            'gte' => $value,
                        ],
                    ],
                ];
                $searchQueryBuilder->addFilter($clause);
                break;
            case Operators::LOWER_THAN:
                $this->assertValueIsNumeric($attributeCode, $value);
                $clause = $this->getColumnRowChannelAndLocaleFilters($attributeCode, $column, $rowCode, $locale, $channel);
                $clause['nested']['query']['bool']['filter'][] = [
                    'range' => [
                        \sprintf('%s.value-%s', $attributePath, $column->dataType()->asString()) => [
                            'lt' => $value,
                        ],
                    ],
                ];
                $searchQueryBuilder->addFilter($clause);
                break;
            case Operators::LOWER_OR_EQUAL_THAN:
                $this->assertValueIsNumeric($attributeCode, $value);
                $clause = $this->getColumnRowChannelAndLocaleFilters($attributeCode, $column, $rowCode, $locale, $channel);
                $clause['nested']['query']['bool']['filter'][] = [
                    'range' => [
                        \sprintf('%s.value-%s', $attributePath, $column->dataType()->asString()) => [
                            'lte' => $value,
                        ],
                    ],
                ];
                $searchQueryBuilder->addFilter($clause);
                break;
            case Operators::IN_LIST:
                $this->assertValueIsAnArrayOfStrings($attributeCode, $value);
                $clause = $this->getColumnRowChannelAndLocaleFilters($attributeCode, $column, $rowCode, $locale, $channel);
                $clause['nested']['query']['bool']['filter'][] = [
                    'terms' => [
                        \sprintf('%s.value-%s', $attributePath, $column->dataType()->asString()) => $value,
                    ],
                ];
                $searchQueryBuilder->addFilter($clause);
                break;
            case Operators::NOT_IN_LIST:
                $this->assertValueIsAnArrayOfStrings($attributeCode, $value);
                $clause = $this->getColumnRowChannelAndLocaleFilters($attributeCode, $column, $rowCode, $locale, $channel);
                if ($isFirstColumn) {
                    // Table must not be empty
                    $searchQueryBuilder->addFilter($clause);
                } else {
                    $clause['nested']['query']['bool']['filter'][] = [
                        'exists' => [
                            'field' => \sprintf('%s.value-%s', $attributePath, $column->dataType()->asString()),
                        ],
                    ];
                    $searchQueryBuilder->addFilter($clause);
                }

            $clause = $this->getColumnRowChannelAndLocaleFilters($attributeCode, $column, $rowCode, $locale, $channel);
            $clause['nested']['query']['bool']['filter'][] = [
                'terms' => [
                    \sprintf('%s.value-%s', $attributePath, $column->dataType()->asString()) => $value,
                ],
            ];
            $searchQueryBuilder->addMustNot($clause);
            break;
            case Operators::NOT_EQUAL:
                $this->assertValueIsScalar($attributeCode, $value);
                $filterClause = $this->getColumnRowChannelAndLocaleFilters($attributeCode, $column, $rowCode, $locale, $channel);
                $filterClause['nested']['query']['bool']['filter'][] = [
                    'exists' => [
                        'field' => \sprintf('%s.value-%s', $attributePath, $column->dataType()->asString()),
                    ],
                ];
                $searchQueryBuilder->addFilter($filterClause);

                $clause = $this->getColumnRowChannelAndLocaleFilters($attributeCode, $column, $rowCode, $locale, $channel);
                $clause['nested']['query']['bool']['filter'][] = [
                    'term' => [
                        \sprintf('%s.value-%s', $attributePath, $column->dataType()->asString()) => $value,
                    ],
                ];
                $searchQueryBuilder->addMustNot($clause);
                break;
            case Operators::DOES_NOT_CONTAIN:
                $this->assertValueIsString($attributeCode, $value);
                $filterClause = $this->getColumnRowChannelAndLocaleFilters($attributeCode, $column, $rowCode, $locale, $channel);
                $filterClause['nested']['query']['bool']['filter'][] = [
                    'exists' => [
                        'field' => \sprintf('%s.value-%s', $attributePath, $column->dataType()->asString()),
                    ],
                ];
                $searchQueryBuilder->addFilter($filterClause);

                $clause = $this->getColumnRowChannelAndLocaleFilters($attributeCode, $column, $rowCode, $locale, $channel);
                $clause['nested']['query']['bool']['filter'][] = [
                    'wildcard' => [
                        \sprintf('%s.value-%s', $attributePath, $column->dataType()->asString()) => [
                            'value' => \sprintf('*%s*', $value),
                        ],
                    ],
                ];
                $searchQueryBuilder->addMustNot($clause);
                break;
            default:
                throw InvalidOperatorException::notSupported($operator, self::class);
        }
    }

    private function getColumnRowChannelAndLocaleFilters(
        string $attributeCode,
        ColumnDefinition $column,
        ?string $rowCode,
        ?string $locale,
        ?string $channel
    ): array {
        $attributePath = \sprintf('table_values.%s', $attributeCode);
        $clause = $this->getEmptyFilter($attributeCode);

        $clause['nested']['query']['bool']['filter'][] = [
            'term' => [
                \sprintf('%s.column', $attributePath) => $column->code()->asString(),
            ],
        ];
        if (null !== $rowCode) {
            $clause['nested']['query']['bool']['filter'][] = [
                'term' => [
                    \sprintf('%s.row', $attributePath) => $rowCode,
                ],
            ];
        }
        if (null !== $locale) {
            $clause['nested']['query']['bool']['filter'][] = [
                'term' => [
                    \sprintf('%s.locale', $attributePath) => $locale,
                ],
            ];
        }
        if (null !== $channel) {
            $clause['nested']['query']['bool']['filter'][] = [
                'term' => [
                    \sprintf('%s.channel', $attributePath) => $channel,
                ],
            ];
        }

        return $clause;
    }

    private function getChannelAndLocaleFilters(
        string $attributeCode,
        ?string $locale,
        ?string $channel
    ): array {
        $attributePath = \sprintf('table_values.%s', $attributeCode);
        $clause = $this->getEmptyFilter($attributeCode);
        if (null !== $locale) {
            $clause['nested']['query']['bool']['filter'][] = [
                'term' => [
                    \sprintf('%s.locale', $attributePath) => $locale,
                ],
            ];
        }
        if (null !== $channel) {
            $clause['nested']['query']['bool']['filter'][] = [
                'term' => [
                    \sprintf('%s.channel', $attributePath) => $channel,
                ],
            ];
        }

        return $clause;
    }

    private function getEmptyFilter(string $attributeCode)
    {
        return [
            'nested' => [
                'path' => \sprintf('table_values.%s', $attributeCode),
                'query' => [
                    'bool' => [
                        'filter' => [],
                    ],
                ],
                'ignore_unmapped' => true,
            ],
        ];
    }

    private function assertValueIsString(string $attributeCode, $value): void
    {
        if (!is_string($value)) {
            throw InvalidPropertyTypeException::stringExpected($attributeCode, static::class, $value);
        }
    }

    private function assertValueIsNumeric(string $attributeCode, $value): void
    {
        if (!is_numeric($value)) {
            throw InvalidPropertyTypeException::numericExpected($attributeCode, static::class, $value);
        }
    }

    private function assertValueIsScalar(string $attributeCode, $value): void
    {
        if (!is_scalar($value)) {
            throw InvalidPropertyTypeException::scalarExpected($attributeCode, static::class, $value);
        }
    }

    private function assertValueIsAnArrayOfStrings(string $attributeCode, $value): void
    {
        if (!is_array($value)) {
            throw InvalidPropertyTypeException::arrayExpected($attributeCode, static::class, $value);
        }

        foreach ($value as $aValue) {
            if (!is_string($aValue)) {
                throw InvalidPropertyTypeException::arrayOfStringsExpected($attributeCode, static::class, $value);
            }
        }
    }

    private function convertMeasurementValue(string $attributeCode, MeasurementColumn $column, $value): string
    {
        Assert::notNull($this->measureConverter);
        if (!\is_array($value)) {
            throw InvalidPropertyTypeException::arrayExpected($attributeCode, static::class, $value);
        }
        if (!\array_key_exists('amount', $value)) {
            throw InvalidPropertyTypeException::arrayKeyExpected(
                $attributeCode,
                'amount',
                static::class,
                $value
            );
        }

        if (!\array_key_exists('unit', $value)) {
            throw InvalidPropertyTypeException::arrayKeyExpected(
                $attributeCode,
                'unit',
                static::class,
                $value
            );
        }

        if (!\is_numeric($value['amount'])) {
            throw InvalidPropertyTypeException::validArrayStructureExpected(
                $attributeCode,
                sprintf('key "amount" has to be a numeric, "%s" given', \json_encode($value['amount'])),
                static::class,
                $value
            );
        }

        if (!\is_string($value['unit'])) {
            throw InvalidPropertyTypeException::validArrayStructureExpected(
                $attributeCode,
                sprintf('key "unit" has to be a string, "%s" given', gettype($value['unit'])),
                static::class,
                $value
            );
        }

        $measurementFamilyCode = $column->measurementFamilyCode();
        try {
            return $this->measureConverter->convertAmountInStandardUnit(
                $measurementFamilyCode,
                (string) $value['amount'],
                $value['unit']
            );
        } catch (MeasurementException $e) {
            throw InvalidPropertyException::validEntityCodeExpected(
                $attributeCode,
                $e->errorField(),
                $e->getMessage(),
                static::class,
                $e->errorValue()
            );
        }
    }
}
