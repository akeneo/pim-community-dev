<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Elasticsearch\Filter\Attribute;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

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

                $clauses = array_map(function ($attributePath) use ($value) {
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
                $clauses = array_map(function ($attributePath) use ($value) {
                    return [
                        'exists' => [
                            'field' => $attributePath
                        ]
                    ];
                }, $attributePaths);

                $clause = $this->addBooleanClause($clauses);
                $this->searchQueryBuilder->addMustNot($clause);
                break;
            case Operators::IS_NOT_EMPTY:
                $clauses = array_map(function ($attributePath) use ($value) {
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
        if (!is_numeric($value) && null !== $value) {
            throw InvalidPropertyTypeException::numericExpected($attribute->getCode(), static::class, $value);
        }
    }
}
