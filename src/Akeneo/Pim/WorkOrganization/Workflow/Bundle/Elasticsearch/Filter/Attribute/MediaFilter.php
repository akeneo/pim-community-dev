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

/**
 * Proposal media filter for an Elasticsearch query.
 */
class MediaFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    const PATH_SUFFIX = 'original_filename';

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

        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($attribute, $value);
        }

        $attributePaths = $this->attributePathResolver->getAttributePaths($attribute);

        switch ($operator) {
            case Operators::STARTS_WITH:
                $clauses = array_map(function ($attributePath) use ($value) {
                    return [
                        'query_string' => [
                            'default_field' => $attributePath . '.' . static::PATH_SUFFIX,
                            'query'         => $value . '*',
                        ],
                    ];
                }, $attributePaths);

                $clause = $this->addBooleanClause($clauses);
                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::CONTAINS:
                $clauses = array_map(function ($attributePath) use ($value) {
                    return [
                        'query_string' => [
                            'default_field' => $attributePath . '.' . static::PATH_SUFFIX,
                            'query'         => '*' . $value . '*',
                        ],
                    ];
                }, $attributePaths);

                $clause = $this->addBooleanClause($clauses);
                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::DOES_NOT_CONTAIN:
                $clauses = array_map(function ($attributePath) use ($value) {
                    return [
                        'query_string' => [
                            'default_field' => $attributePath . '.' . static::PATH_SUFFIX,
                            'query'         => '*' . $value . '*',
                        ],
                    ];
                }, $attributePaths);

                $mustNotClause = $this->addBooleanClause($clauses);

                $clauses = array_map(function ($attributePath) {
                    return [
                        'exists' => ['field' => $attributePath],
                    ];
                }, $attributePaths);
                $filterClause = $this->addBooleanClause($clauses);

                $this->searchQueryBuilder->addMustNot($mustNotClause);
                $this->searchQueryBuilder->addFilter($filterClause);
                break;

            case Operators::EQUALS:
                $clauses = array_map(function ($attributePath) use ($value) {
                    return [
                        'term' => [
                            $attributePath . '.' . static::PATH_SUFFIX => $value,
                        ],
                    ];
                }, $attributePaths);

                $clause = $this->addBooleanClause($clauses);
                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::NOT_EQUAL:
                $clauses = array_map(function ($attributePath) use ($value) {
                    return [
                        'term' => [
                            $attributePath . '.' . static::PATH_SUFFIX => $value,
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

            case Operators::IS_EMPTY:
                $clauses = array_map(function ($attributePath) {
                    return [
                        'exists' => [
                            'field' => $attributePath,
                        ],
                    ];
                }, $attributePaths);

                $clause = $this->addBooleanClause($clauses);
                $this->searchQueryBuilder->addMustNot($clause);
                break;

            case Operators::IS_NOT_EMPTY:
                $clauses = array_map(function ($attributePath) {
                    return [
                        'exists' => [
                            'field' => $attributePath,
                        ],
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
     * Checks if the value is valid.
     *
     * @param AttributeInterface $attribute
     * @param mixed              $value
     */
    protected function checkValue(AttributeInterface $attribute, $value)
    {
        FieldFilterHelper::checkString($attribute->getCode(), $value, self::class);
    }
}
