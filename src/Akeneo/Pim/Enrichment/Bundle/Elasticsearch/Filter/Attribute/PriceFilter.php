<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Attribute;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\FindActivatedCurrenciesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\AttributeFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Validator\ElasticsearchFilterValidator;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Price filter for an Elasticsearch query.
 *
 * Prior to 1.7 and before:
 * The IS_EMPTY and IS_NOT_EMPTY operators had two different behaviors depending on the currency specified when adding
 * the filter.
 *
 * Let's examine the behavior for the IS_EMPTY operator:
 *
 * If the currency was not specified, the filter would select every products where the price collection was empty (for
 * the given locale and scope).
 *
 * If the currency was specified while calling `addAttributeFilter`, then the filter would select every products for
 * which there was no price available for this currency within the price collection.
 *
 * Same holds true for the IS_NOT_EMPTY operator.
 *
 * The IS_EMPTY Operator is now deprecated, please use IS_EMPTY_ON_ALL_CURRENCIES instead.
 * The IS_NOT_EMPTY Operator is now deprecated, please use IS_NOT_EMPTY_ON_AT_LEAST_ONE_CURRENCY instead.
 *
 * Note: the IS_EMPTY and IS_NOT_EMPTY operators are now mapped to the first behavior described above, meaning that it
 * takes does not take any currency as a parameter and only checks if the price collection is empty for the (given
 * locale and scope).
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceFilter extends AbstractAttributeFilter implements AttributeFilterInterface
{
    protected FindActivatedCurrenciesInterface $findActivatedCurrencies;

    public function __construct(
        ElasticsearchFilterValidator $filterValidator,
        FindActivatedCurrenciesInterface $findActivatedCurrencies,
        array $supportedAttributeTypes = [],
        array $supportedOperators = []
    ) {
        $this->filterValidator = $filterValidator;
        $this->findActivatedCurrencies = $findActivatedCurrencies;
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
    ): self {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        $this->checkLocaleAndChannel($attribute, $locale, $channel);

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

        $attributePath = $this->getAttributePath($attribute, $locale, $channel);

        switch ($operator) {
            case Operators::LOWER_THAN:
                $attributePath .= '.' . $value['currency'];
                $clause = [
                    'range' => [
                        $attributePath => ['lt' => $value['amount']],
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::LOWER_OR_EQUAL_THAN:
                $attributePath .= '.' . $value['currency'];
                $clause = [
                    'range' => [
                        $attributePath => ['lte' => $value['amount']],
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::EQUALS:
                $attributePath .= '.' . $value['currency'];
                $clause = [
                    'term' => [
                        $attributePath => $value['amount'],
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::NOT_EQUAL:
                $attributePath .= '.' . $value['currency'];
                $mustNotClause = [
                    'term' => [
                        $attributePath => $value['amount'],
                    ],
                ];
                $filterClause = [
                    'exists' => [
                        'field' => $attributePath,
                    ],
                ];
                $this->searchQueryBuilder->addMustNot($mustNotClause);
                $this->searchQueryBuilder->addFilter($filterClause);
                break;

            case Operators::GREATER_OR_EQUAL_THAN:
                $attributePath .= '.' . $value['currency'];
                $clause = [
                    'range' => [
                        $attributePath => ['gte' => $value['amount']],
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::GREATER_THAN:
                $attributePath .= '.' . $value['currency'];
                $clause = [
                    'range' => [
                        $attributePath => ['gt' => $value['amount']],
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::IS_EMPTY:
            case Operators::IS_EMPTY_ON_ALL_CURRENCIES:
                $clause = [
                    'exists' => [
                        'field' => $attributePath,
                    ],
                ];
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

            case Operators::IS_EMPTY_FOR_CURRENCY:
                $attributePath .= '.' . $value['currency'];
                $filterClause = [
                    'exists' => [
                        'field' => $attributePath,
                    ],
                ];
                $this->searchQueryBuilder->addMustNot($filterClause);

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
            case Operators::IS_NOT_EMPTY_ON_AT_LEAST_ONE_CURRENCY:
                $clause = [
                    'exists' => [
                        'field' => $attributePath,
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::IS_NOT_EMPTY_FOR_CURRENCY:
                $attributePath .= '.' . $value['currency'];
                $filterClause = [
                    'exists' => [
                        'field' => $attributePath,
                    ],
                ];
                $this->searchQueryBuilder->addFilter($filterClause);
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
    protected function checkAmount(AttributeInterface $attribute, $data): void
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
                sprintf('key "amount" has to be a numeric, "%s" given', gettype($data['amount'])),
                $data
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
    protected function checkCurrency(AttributeInterface $attribute, $data): void
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
            !in_array($data['currency'], $this->findActivatedCurrencies->forAllChannels())
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
