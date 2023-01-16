<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterHelper;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\Elasticsearch\QueryString;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Identifier filter for an Elasticsearch query
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    const IDENTIFIER_KEY = 'identifier';

    /**
     * @param array $supportedFields
     * @param array $supportedOperators
     */
    public function __construct(
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $channel = null, $options = [])
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($field, $operator, $value);
        }

        $this->applyFilter($field, $operator, $value);

        return $this;
    }

    /**
     * Checks the identifier is a string or an array depending on the operator
     *
     * @param string $property
     * @param string $operator
     * @param mixed  $value
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkValue($property, $operator, $value)
    {
        if (Operators::IN_LIST === $operator || Operators::NOT_IN_LIST === $operator) {
            FieldFilterHelper::checkArrayOfStrings($property, $value, self::class);
        } else {
            FieldFilterHelper::checkString($property, $value, self::class);
        }
    }

    /**
     * Apply the filtering conditions to the search query builder
     *
     * @param string $field
     * @param string $operator
     * @param mixed  $value
     */
    protected function applyFilter($field, $operator, $value)
    {
        $product = str_replace('\\', '\\\\', ProductInterface::class);
        $productModel = str_replace('\\', '\\\\', ProductModelInterface::class);
        $skuField = 'values.sku-text.<all_channels>.<all_locales>';
        $productModelField = 'identifier';

        switch ($operator) {
            case Operators::STARTS_WITH:
                $clause1 = [
                    'bool' => [
                        'should' => [
                            [
                                'query_string' => [
                                    'default_field' => $skuField,
                                    'query'         => QueryString::escapeValue($value) . '*',
                                ],
                            ],
                            [
                                'query_string' => [
                                    'default_field' => 'document_type',
                                    'query'         => $product,
                                ],
                            ],
                        ],
                        'minimum_should_match' => 2,
                    ],
                ];
                $clause2 = [
                    'bool' => [
                        'should' => [
                            [
                                'query_string' => [
                                    'default_field' => $productModelField,
                                    'query'         => QueryString::escapeValue($value) . '*',
                                ],
                            ],
                            [
                                'query_string' => [
                                    'default_field' => 'document_type',
                                    'query'         => $productModel,
                                ],
                            ],
                        ],
                        'minimum_should_match' => 2,
                    ],
                ];

                $this->searchQueryBuilder->addFilter(
                    [
                        'bool' => [
                            'should' => [$clause1, $clause2],
                            'minimum_should_match' => 1,
                        ],
                    ]
                );
                break;

            case Operators::CONTAINS:
                $clause = [
                    'query_string' => [
                        'default_field' => $field,
                        'query'         => '*' . QueryString::escapeValue($value) . '*',
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::DOES_NOT_CONTAIN:
                $mustNotClause = [
                    'query_string' => [
                        'default_field' => $field,
                        'query'         => '*' . QueryString::escapeValue($value) . '*',
                    ],
                ];

                $filterClause = [
                    'exists' => ['field' => $field],
                ];

                $this->searchQueryBuilder->addMustNot($mustNotClause);
                $this->searchQueryBuilder->addFilter($filterClause);
                break;

            case Operators::EQUALS:
                $clause = [
                    'term' => [
                        $field => $value,
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::NOT_EQUAL:
                $mustNotClause = [
                    'term' => [
                        $field => $value,
                    ],
                ];

                $filterClause = [
                    'exists' => [
                        'field' => $field,
                    ],
                ];
                $this->searchQueryBuilder->addMustNot($mustNotClause);
                $this->searchQueryBuilder->addFilter($filterClause);
                break;

            case Operators::IN_LIST:
                $clause = [
                    'terms' => [
                        $field => $value,
                    ],
                ];

                $this->searchQueryBuilder->addFilter($clause);
                break;

            case Operators::NOT_IN_LIST:
                $clause = [
                    'terms' => [
                        $field => $value,
                    ],
                ];

                $this->searchQueryBuilder->addMustNot($clause);
                break;

            case Operators::IS_EMPTY:
                $clause = [
                    'exists' => ['field' => $field,],
                ];

                $this->searchQueryBuilder->addMustNot($clause);
                break;

            case Operators::IS_NOT_EMPTY:
                $clause = [
                    'exists' => ['field' => $field],
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;

            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }
    }
}
