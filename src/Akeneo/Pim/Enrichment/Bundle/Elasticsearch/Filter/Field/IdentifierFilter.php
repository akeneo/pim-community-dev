<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
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

        $this->checkValue($field, $operator, $value);
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
            FieldFilterHelper::checkArray($property, $value, self::class);
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
        switch ($operator) {
            case Operators::STARTS_WITH:
                $clause = [
                    'query_string' => [
                        'default_field' => $field,
                        'query'         => QueryString::escapeValue($value) . '*',
                    ],
                ];
                $this->searchQueryBuilder->addFilter($clause);
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

            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }
    }
}
