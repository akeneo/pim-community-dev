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
use Akeneo\Tool\Bundle\MeasureBundle\Convert\MeasureConverter;
use Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Proposal metric filter for an Elasticsearch query
 */
class MetricFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    const PATH_SUFFIX = 'base_data';

    /** @var MeasureManager */
    protected $measureManager;

    /** @var MeasureConverter */
    protected $measureConverter;

    /**
     * @param ProposalAttributePathResolver $attributePathResolver
     * @param MeasureManager                $measureManager
     * @param MeasureConverter              $measureConverter
     * @param string[]                      $supportedAttributeTypes
     * @param string[]                      $supportedOperators
     */
    public function __construct(
        ProposalAttributePathResolver $attributePathResolver,
        MeasureManager $measureManager,
        MeasureConverter $measureConverter,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        $this->supportedAttributeTypes = $supportedAttributeTypes;
        $this->measureManager = $measureManager;
        $this->measureConverter = $measureConverter;
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
            $value = $this->convertValue($attribute, $value);
        }

        $attributePaths = $this->attributePathResolver->getAttributePaths($attribute);

        switch ($operator) {
            case Operators::LOWER_THAN:
                $clauses = array_map(function ($attributePath) use ($value) {
                    return [
                        'range' => [
                            $attributePath . '.' . self::PATH_SUFFIX => ['lt' => $value]
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
                            $attributePath . '.' . self::PATH_SUFFIX => ['lte' => $value]
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
                            $attributePath . '.' . self::PATH_SUFFIX => $value
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
                            $attributePath . '.' . self::PATH_SUFFIX => $value
                        ]
                    ];
                }, $attributePaths);
                $mustNotClause = $this->addBooleanClause($clauses);

                $clauses = array_map(function ($attributePath) {
                    return [
                        'exists' => [
                            'field' => $attributePath . '.' . self::PATH_SUFFIX,
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
                            $attributePath . '.' . self::PATH_SUFFIX => ['gte' => $value]
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
                            $attributePath . '.' . self::PATH_SUFFIX => ['gt' => $value]
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
                            'field' => $attributePath . '.' . self::PATH_SUFFIX
                        ]
                    ];
                }, $attributePaths);

                $clause = $this->addBooleanClause($clauses);
                $this->searchQueryBuilder->addMustNot($clause);
                break;

            case Operators::IS_NOT_EMPTY:
                $clauses = array_map(function ($attributePath) {
                    return [
                        'exists' => [
                            'field' => $attributePath . '.' . self::PATH_SUFFIX
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
     * Check if the given value is valid
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @throws InvalidPropertyTypeException
     * @throws InvalidPropertyException
     */
    protected function checkValue(AttributeInterface $attribute, $data)
    {
        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected($attribute->getCode(), static::class, $data);
        }

        if (!array_key_exists('amount', $data)) {
            throw InvalidPropertyTypeException::arrayKeyExpected(
                $attribute->getCode(),
                'amount',
                static::class,
                $data
            );
        }

        if (!array_key_exists('unit', $data)) {
            throw InvalidPropertyTypeException::arrayKeyExpected(
                $attribute->getCode(),
                'unit',
                static::class,
                $data
            );
        }

        if (null === $data['amount'] || !is_numeric($data['amount'])) {
            throw InvalidPropertyTypeException::validArrayStructureExpected(
                $attribute->getCode(),
                sprintf('key "amount" has to be a numeric, "%s" given', gettype($data['amount'])),
                static::class,
                $data
            );
        }

        if (!is_string($data['unit'])) {
            throw InvalidPropertyTypeException::validArrayStructureExpected(
                $attribute->getCode(),
                sprintf('key "unit" has to be a string, "%s" given', gettype($data['unit'])),
                static::class,
                $data
            );
        }

        if (!array_key_exists(
            $data['unit'],
            $this->measureManager->getUnitSymbolsForFamily($attribute->getMetricFamily())
        )) {
            throw InvalidPropertyException::validEntityCodeExpected(
                $attribute->getCode(),
                'unit',
                sprintf(
                    'The unit does not exist in the attribute\'s family "%s"',
                    $attribute->getMetricFamily()
                ),
                static::class,
                $data['unit']
            );
        }
    }

    /**
     * Converts the given value to the base_unit configured in the family.
     *
     * @param AttributeInterface $attribute
     * @param array              $data
     *
     * @return float
     */
    protected function convertValue(AttributeInterface $attribute, array $data)
    {
        $this->measureConverter->setFamily($attribute->getMetricFamily());

        return $this->measureConverter->convertBaseToStandard($data['unit'], $data['amount']);
    }
}
