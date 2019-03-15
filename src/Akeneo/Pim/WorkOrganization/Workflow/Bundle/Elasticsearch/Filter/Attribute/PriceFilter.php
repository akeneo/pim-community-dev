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

use Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Proposal price filter for an Elasticsearch query.
 */
class PriceFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    /** @var CurrencyRepositoryInterface */
    protected $currencyRepository;

    /**
     * @param ProposalAttributePathResolver $attributePathResolver
     * @param CurrencyRepositoryInterface   $currencyRepository
     * @param string[]                      $supportedAttributeTypes
     * @param string[]                      $supportedOperators
     */
    public function __construct(
        ProposalAttributePathResolver $attributePathResolver,
        CurrencyRepositoryInterface $currencyRepository,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        $this->attributePathResolver = $attributePathResolver;
        $this->currencyRepository = $currencyRepository;
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

        if (Operators::IS_EMPTY_FOR_CURRENCY === $operator ||
            Operators::IS_NOT_EMPTY_FOR_CURRENCY === $operator
        ) {
            $this->checkCurrency($attribute, $value);
        } elseif (Operators::IS_EMPTY_ON_ALL_CURRENCIES !== $operator &&
            Operators::IS_EMPTY !== $operator &&
            Operators::IS_NOT_EMPTY_ON_AT_LEAST_ONE_CURRENCY !== $operator &&
            Operators::IS_NOT_EMPTY !== $operator
        ) {
            $this->checkAmount($attribute, $value);
            $this->checkCurrency($attribute, $value);
        }

        $attributePaths = $this->attributePathResolver->getAttributePaths($attribute);

        switch ($operator) {
            case Operators::LOWER_THAN:
                $clauses = array_map(function ($attributePath) use ($value) {
                    return [
                        'range' => [
                            $attributePath . '.' . $value['currency'] => ['lt' => $value['amount']],
                        ],
                    ];
                }, $attributePaths);

                $clause = $this->addBooleanClause($clauses);
                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::LOWER_OR_EQUAL_THAN:
                $clauses = array_map(function ($attributePath) use ($value) {
                    return [
                        'range' => [
                            $attributePath . '.' . $value['currency'] => ['lte' => $value['amount']],
                        ],
                    ];
                }, $attributePaths);

                $clause = $this->addBooleanClause($clauses);
                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::EQUALS:
                $clauses = array_map(function ($attributePath) use ($value) {
                    return [
                        'term' => [
                            $attributePath . '.' . $value['currency'] => $value['amount'],
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
                            $attributePath . '.' . $value['currency'] => $value['amount'],
                        ],
                    ];
                }, $attributePaths);
                $mustNotClause = $this->addBooleanClause($clauses);

                $clauses = array_map(function ($attributePath) use ($value) {
                    return [
                        'exists' => [
                            'field' => $attributePath . '.' . $value['currency'],
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
                            $attributePath . '.' . $value['currency'] => ['gte' => $value['amount']],
                        ],
                    ];
                }, $attributePaths);

                $clause = $this->addBooleanClause($clauses);
                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::GREATER_THAN:
                $clauses = array_map(function ($attributePath) use ($value) {
                    return [
                        'range' => [
                            $attributePath . '.' . $value['currency'] => ['gt' => $value['amount']],
                        ],
                    ];
                }, $attributePaths);

                $clause = $this->addBooleanClause($clauses);
                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::IS_EMPTY:
            case Operators::IS_EMPTY_ON_ALL_CURRENCIES:
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

            case Operators::IS_EMPTY_FOR_CURRENCY:
                $clauses = array_map(function ($attributePath) use ($value) {
                    return [
                        'exists' => [
                            'field' => $attributePath . '.' . $value['currency'],
                        ],
                    ];
                }, $attributePaths);

                $clause = $this->addBooleanClause($clauses);
                $this->searchQueryBuilder->addMustNot($clause);
                break;

            case Operators::IS_NOT_EMPTY:
            case Operators::IS_NOT_EMPTY_ON_AT_LEAST_ONE_CURRENCY:
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

            case Operators::IS_NOT_EMPTY_FOR_CURRENCY:
                $clauses = array_map(function ($attributePath) use ($value) {
                    return [
                        'exists' => [
                            'field' => $attributePath . '.' . $value['currency'],
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
     * Checks that the value is correctly set
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @throws InvalidPropertyTypeException
     * @throws InvalidPropertyException
     */
    protected function checkAmount(AttributeInterface $attribute, $data)
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

        if (!is_numeric($data['amount'])) {
            throw InvalidPropertyTypeException::numericExpected(
                $attribute->getCode(),
                static::class,
                $data['amount']
            );
        }
    }

    /**
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @throws InvalidPropertyTypeException
     * @throws InvalidPropertyException
     */
    protected function checkCurrency(AttributeInterface $attribute, $data)
    {
        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected($attribute->getCode(), static::class, $data);
        }

        if (!array_key_exists('currency', $data)) {
            throw InvalidPropertyTypeException::arrayKeyExpected(
                $attribute->getCode(),
                'currency',
                static::class,
                $data
            );
        }

        if ('' === $data['currency'] ||
            !is_string($data['currency']) ||
            !in_array($data['currency'], $this->currencyRepository->getActivatedCurrencyCodes())
        ) {
            throw InvalidPropertyException::validEntityCodeExpected(
                $attribute->getCode(),
                'currency',
                'The currency does not exist',
                static::class,
                $data['currency']
            );
        }
    }
}
