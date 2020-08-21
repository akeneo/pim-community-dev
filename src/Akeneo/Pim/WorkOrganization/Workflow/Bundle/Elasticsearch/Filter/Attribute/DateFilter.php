<?php

declare(strict_types=1);

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\Filter\Attribute;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterHelper;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Proposal date filter for an Elasticsearch query
 */
class DateFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    const DATETIME_FORMAT = 'Y-m-d';
    const HUMAN_DATETIME_FORMAT = "yyyy-mm-dd";

    /**
     * @param ProposalAttributePathResolver $attributePathResolver
     * @param string[]                      $supportedAttributeTypes
     * @param string[]                      $supportedOperators
     */
    public function __construct(
        ProposalAttributePathResolver $attributePathResolver,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        $this->attributePathResolver = $attributePathResolver;
        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(
        AttributeInterface $attribute,
        $operator,
        $value,
        $locale = null,
        $channel = null,
        $options = []
    ) {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        $attributeCode = $attribute->getCode();

        $this->checkValue($operator, $attributeCode, $value);

        $attributePaths = $this->attributePathResolver->getAttributePaths($attribute);

        switch ($operator) {
            case Operators::EQUALS:
                $clauses = array_map(function ($attributePath) use ($attributeCode, $value) {
                    return [
                        'term' => [
                            $attributePath => $this->getFormattedDate($attributeCode, $value)
                        ]
                    ];
                }, $attributePaths);

                $clause = $this->addBooleanClause($clauses);
                $this->searchQueryBuilder->addFilter($clause);

                break;
            case Operators::LOWER_THAN:
                $clauses = array_map(function ($attributePath) use ($attributeCode, $value) {
                    return [
                        'range' => [
                            $attributePath => [
                                'lt' => $this->getFormattedDate($attributeCode, $value),
                            ]
                        ]
                    ];
                }, $attributePaths);

                $clause = $this->addBooleanClause($clauses);
                $this->searchQueryBuilder->addFilter($clause);

                break;
            case Operators::GREATER_THAN:
                $clauses = array_map(function ($attributePath) use ($attributeCode, $value) {
                    return [
                        'range' => [
                            $attributePath => [
                                'gt' => $this->getFormattedDate($attributeCode, $value),
                            ]
                        ]
                    ];
                }, $attributePaths);

                $clause = $this->addBooleanClause($clauses);
                $this->searchQueryBuilder->addFilter($clause);

                break;
            case Operators::BETWEEN:
                $clauses = array_map(function ($attributePath) use ($attributeCode, $value) {
                    $values = array_values($value);

                    return [
                        'range' => [
                            $attributePath => [
                                'gte' => $this->getFormattedDate($attributeCode, $values[0]),
                                'lte' => $this->getFormattedDate($attributeCode, $values[1]),
                            ]
                        ]
                    ];
                }, $attributePaths);

                $clause = $this->addBooleanClause($clauses);
                $this->searchQueryBuilder->addFilter($clause);

                break;
            case Operators::NOT_BETWEEN:
                $clauses = array_map(function ($attributePath) use ($attributeCode, $value) {
                    $values = array_values($value);

                    return [
                        'range' => [
                            $attributePath => [
                                'gte' => $this->getFormattedDate($attributeCode, $values[0]),
                                'lte' => $this->getFormattedDate($attributeCode, $values[1]),
                            ]
                        ]
                    ];
                }, $attributePaths);
                $betweenClause = $this->addBooleanClause($clauses);

                $clauses = array_map(function ($attributePath) {
                    return [
                        'exists' => ['field' => $attributePath]
                    ];
                }, $attributePaths);
                $existsClause = $this->addBooleanClause($clauses);

                $this->searchQueryBuilder->addMustNot($betweenClause);
                $this->searchQueryBuilder->addFilter($existsClause);

                break;
            case Operators::IS_EMPTY:
                $clauses = array_map(function ($attributePath) {
                    return [
                        'exists' => ['field' => $attributePath]
                    ];
                }, $attributePaths);
                $existsClause = $this->addBooleanClause($clauses);

                $this->searchQueryBuilder->addMustNot($existsClause);

                $attributeInEntityClauses = [
                    [
                        'terms' => [
                            self::ATTRIBUTES_FOR_THIS_LEVEL_ES_ID => [$attribute->getCode()],
                        ],
                    ],
                    [
                        'terms' => [
                            self::ATTRIBUTES_OF_ANCESTORS_ES_ID => [$attribute->getCode()],
                        ],
                    ]
                ];
                $this->searchQueryBuilder->addFilter(
                    [
                        'bool' => [
                            'should' => $attributeInEntityClauses,
                            'minimum_should_match' => 1,
                        ],
                    ]
                );
                break;

            case Operators::IS_NOT_EMPTY:
                $clauses = array_map(function ($attributePath) {
                    return [
                        'exists' => ['field' => $attributePath]
                    ];
                }, $attributePaths);
                $existsClause = $this->addBooleanClause($clauses);

                $this->searchQueryBuilder->addFilter($existsClause);

                break;
            case Operators::NOT_EQUAL:
                $clauses = array_map(function ($attributePath) use ($attributeCode, $value) {
                    return [
                        'term' => [
                            $attributePath => $this->getFormattedDate($attributeCode, $value)
                        ]
                    ];
                }, $attributePaths);
                $mustNotClause = $this->addBooleanClause($clauses);

                $clauses = array_map(function ($attributePath) {
                    return [
                        'exists' => ['field' => $attributePath]
                    ];
                }, $attributePaths);
                $existsClause = $this->addBooleanClause($clauses);

                $this->searchQueryBuilder->addMustNot($mustNotClause);
                $this->searchQueryBuilder->addFilter($existsClause);

                break;
            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }

        return $this;
    }

    /**
     * @param string $operator
     * @param string $field
     * @param string|array|\DateTime $value
     */
    protected function checkValue($operator, $field, $value)
    {
        switch ($operator) {
            case Operators::EQUALS:
            case Operators::LOWER_THAN:
            case Operators::GREATER_THAN:
            case Operators::NOT_EQUAL:
                FieldFilterHelper::checkDateTime(
                    $field,
                    $value,
                    static::DATETIME_FORMAT,
                    static::HUMAN_DATETIME_FORMAT,
                    static::class
                );

                break;
            case Operators::BETWEEN:
            case Operators::NOT_BETWEEN:
                if (!is_array($value)) {
                    throw InvalidPropertyTypeException::arrayExpected($field, static::class, $value);
                }

                if (2 !== count($value)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        $field,
                        sprintf('should contain 2 strings with the format "%s"', static::HUMAN_DATETIME_FORMAT),
                        static::class,
                        $value
                    );
                }

                foreach ($value as $singleValue) {
                    FieldFilterHelper::checkDateTime(
                        $field,
                        $singleValue,
                        static::DATETIME_FORMAT,
                        static::HUMAN_DATETIME_FORMAT,
                        static::class
                    );
                }

                break;
            case Operators::IS_EMPTY:
            case Operators::IS_NOT_EMPTY:
                break;
            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }
    }

    /**
     * @param string $field
     * @param string|\DateTime $value
     *
     * @return string
     */
    protected function getFormattedDate($field, $value)
    {
        $dateTime = $value;

        if (!$dateTime instanceof \DateTime) {
            $dateTime = \DateTime::createFromFormat(static::DATETIME_FORMAT, $dateTime);

            if (false === $dateTime || 0 < $dateTime->getLastErrors()['warning_count']) {
                throw InvalidPropertyException::dateExpected(
                    $field,
                    static::HUMAN_DATETIME_FORMAT,
                    static::class,
                    $value
                );
            }
        }

        return $dateTime->format(static::DATETIME_FORMAT);
    }
}
