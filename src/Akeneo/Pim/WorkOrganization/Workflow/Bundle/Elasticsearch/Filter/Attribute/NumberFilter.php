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
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Proposal number filter for an Elasticsearch query
 */
class NumberFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
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
        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->supportedOperators = $supportedOperators;
        $this->attributePathResolver = $attributePathResolver;
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

        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($attribute, $value);
        }

        $attributePaths = $this->attributePathResolver->getAttributePaths($attribute);

        switch ($operator) {
            case Operators::LOWER_THAN:
                $clauses = array_map(function ($attributePath) use ($value) {
                    return [
                        'range' => [
                            $attributePath => ['lt' => $value]
                        ]
                    ];
                }, $attributePaths);

            $clause = $this->addBooleanClause($clauses);
            $this->searchQueryBuilder->addFilter($clause);
            break;
            case Operators::LOWER_OR_EQUAL_THAN:
                $clauses = array_map(function ($attributePath) use ($value) {
                    return [
                        'range' => [
                            $attributePath => ['lte' => $value]
                        ]
                    ];
                }, $attributePaths);

            $clause = $this->addBooleanClause($clauses);
            $this->searchQueryBuilder->addFilter($clause);
            break;
            case Operators::EQUALS:
                $clauses = array_map(function ($attributePath) use ($value) {
                    return [
                        'term' => [
                            $attributePath => $value
                        ]
                    ];
                }, $attributePaths);

            $clause = $this->addBooleanClause($clauses);
            $this->searchQueryBuilder->addFilter($clause);
            break;
            case Operators::NOT_EQUAL:
                $clauses = array_map(function ($attributePath) use ($value) {
                    return [
                        'term' => [
                            $attributePath => $value,
                        ],
                    ];
                }, $attributePaths);
            $mustNotClause = $this->addBooleanClause($clauses);

            $clauses = array_map(function ($attributePath) {
                return [
                    'exists' => [
                        'field' => $attributePath,
                    ],
                ];
            }, $attributePaths);
            $filterClause = $this->addBooleanClause($clauses);

            $this->searchQueryBuilder->addMustNot($mustNotClause);
            $this->searchQueryBuilder->addFilter($filterClause);
            break;
            case Operators::GREATER_OR_EQUAL_THAN:
                $clauses = array_map(function ($attributePath) use ($value) {
                    return [
                        'range' => [
                            $attributePath => ['gte' => $value]
                        ]
                    ];
                }, $attributePaths);

            $clause = $this->addBooleanClause($clauses);
            $this->searchQueryBuilder->addFilter($clause);
            break;
            case Operators::GREATER_THAN:
                $clauses = array_map(function ($attributePath) use ($value) {
                    return [
                        'range' => [
                            $attributePath => ['gt' => $value]
                        ]
                    ];
                }, $attributePaths);

            $clause = $this->addBooleanClause($clauses);
            $this->searchQueryBuilder->addFilter($clause);
            break;
            case Operators::IS_EMPTY:
                $clauses = array_map(function ($attributePath) {
                    return [
                        'exists' => [
                            'field' => $attributePath
                        ]
                    ];
                }, $attributePaths);

            $clause = $this->addBooleanClause($clauses);
            $this->searchQueryBuilder->addMustNot($clause);

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
                        'exists' => [
                            'field' => $attributePath
                        ]
                    ];
                }, $attributePaths);

            $clause = $this->addBooleanClause($clauses);
            $this->searchQueryBuilder->addFilter($clause);
            break;
            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }

        return $this;
    }

    /**
     * Checks that the value is a number.
     *
     * @param AttributeInterface $attribute
     * @param mixed              $value
     */
    protected function checkValue(AttributeInterface $attribute, $value)
    {
        if (!is_numeric($value)) {
            throw InvalidPropertyTypeException::numericExpected($attribute->getCode(), static::class, $value);
        }
    }
}
