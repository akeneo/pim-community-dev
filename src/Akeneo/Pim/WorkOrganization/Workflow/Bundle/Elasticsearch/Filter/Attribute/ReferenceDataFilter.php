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

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ReferenceDataRepositoryResolver;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterHelper;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\ReferenceData\ConfigurationRegistryInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;

/**
 * Proposal reference data filter for an Elasticsearch query
 */
class ReferenceDataFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var ReferenceDataRepositoryResolver */
    private $referenceDataRepositoryResolver;

    /** @var ConfigurationRegistryInterface */
    private $registry;

    /**
     * @param ProposalAttributePathResolver    $attributePathResolver
     * @param ReferenceDataRepositoryResolver  $referenceDataRepositoryResolver
     * @param ConfigurationRegistryInterface   $registry
     * @param array                            $supportedAttributeTypes
     * @param array                            $supportedOperators
     */
    public function __construct(
        ProposalAttributePathResolver $attributePathResolver,
        ReferenceDataRepositoryResolver $referenceDataRepositoryResolver,
        ConfigurationRegistryInterface $registry,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        $this->attributePathResolver = $attributePathResolver;
        $this->referenceDataRepositoryResolver = $referenceDataRepositoryResolver;
        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->supportedOperators = $supportedOperators;
        $this->registry = $registry;
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
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        $referenceDataName = $attribute->getReferenceDataName();

        $isRegistredReferenceData = null !== $referenceDataName &&
            !empty($referenceDataName) &&
            null !== $this->registry->get($attribute->getReferenceDataName());

        return $isRegistredReferenceData;
    }

    /**
     * Check if values are valid
     *
     * @param AttributeInterface $attribute
     * @param mixed              $values
     *
     * @throws InvalidPropertyException
     */
    protected function checkValue(AttributeInterface $attribute, $values)
    {
        FieldFilterHelper::checkArray($attribute->getCode(), $values, static::class);

        foreach ($values as $value) {
            FieldFilterHelper::checkIdentifier($attribute->getCode(), $value, static::class);
        }

        $referenceDataRepository = $this->referenceDataRepositoryResolver->resolve($attribute->getReferenceDataName());
        $existingReferenceData =  $referenceDataRepository->findCodesByIdentifiers($values);
        $referenceDataCodes = array_map(
            function ($referenceData) {
                return $referenceData['code'];
            },
            $existingReferenceData
        );

        $unexistingValues = array_diff($values, $referenceDataCodes);
        if (count($unexistingValues) > 0) {
            $message = sprintf(
                'No reference data "%s" with code "%s" has been found',
                $attribute->getReferenceDataName(),
                reset($unexistingValues)
            );

            throw InvalidPropertyException::validEntityCodeExpected(
                $attribute->getCode(),
                'code',
                $message,
                $referenceDataRepository->getClassName(),
                implode(',', $values)
            );
        }
    }
}
