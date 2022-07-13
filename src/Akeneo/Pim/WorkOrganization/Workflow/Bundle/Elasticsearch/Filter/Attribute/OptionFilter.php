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
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterHelper;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;

/**
 * Proposal option filter for an Elasticsearch query
 */
class OptionFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var AttributeOptionRepositoryInterface */
    protected $attributeOptionRepository;

    /**
     * @param ProposalAttributePathResolver      $attributePathResolver
     * @param AttributeOptionRepositoryInterface $attributeOptionRepository
     * @param string[]                           $supportedAttributeTypes
     * @param string[]                           $supportedOperators
     */
    public function __construct(
        ProposalAttributePathResolver $attributePathResolver,
        AttributeOptionRepositoryInterface $attributeOptionRepository,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->attributeOptionRepository = $attributeOptionRepository;
        $this->supportedOperators = $supportedOperators;
        $this->attributePathResolver = $attributePathResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(
        AttributeInterface $attribute,
        $operator,
        $values,
        $locale = null,
        $channel = null,
        $options = []
    ) {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($attribute, $values);
        }

        $attributePaths = $this->attributePathResolver->getAttributePaths($attribute);

        switch ($operator) {
            case Operators::IN_LIST:
                $clauses = array_map(function ($attributePath) use ($values) {
                    return [
                        'terms' => [
                            $attributePath => $values,
                        ],
                    ];
                }, $attributePaths);

                $clause = $this->addBooleanClause($clauses);
                $this->searchQueryBuilder->addFilter($clause);
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
                            'field' => $attributePath,
                        ],
                    ];
                }, $attributePaths);

                $clause = $this->addBooleanClause($clauses);
                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::NOT_IN_LIST:
                $clauses = array_map(function ($attributePath) use ($values) {
                    return [
                        'terms' => [
                            $attributePath => $values,
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

            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }

        return $this;
    }

    /**
     * Check if values are valid
     *
     * @param AttributeInterface $attribute
     * @param mixed              $values
     *
     * @throws ObjectNotFoundException
     */
    protected function checkValue(AttributeInterface $attribute, $values)
    {
        FieldFilterHelper::checkArray($attribute->getCode(), $values, static::class);

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($attribute->getCode(), $value, static::class);
        }

        $attributeOptions = $this->attributeOptionRepository->findCodesByIdentifiers($attribute->getCode(), $values);
        $optionCodes = array_map(
            function ($attributeOptions) {
                return $attributeOptions['code'];
            },
            $attributeOptions
        );

        $unexistingValues = array_diff($values, $optionCodes);
        if (count($unexistingValues) > 0) {
            throw new ObjectNotFoundException(
                sprintf(
                    'Object "%s" with code "%s" does not exist',
                    $attribute->getBackendType(),
                    reset($unexistingValues)
                )
            );
        }
    }
}
