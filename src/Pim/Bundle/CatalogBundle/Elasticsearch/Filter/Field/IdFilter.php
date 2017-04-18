<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Pim\Component\Catalog\Exception\InvalidOperatorException;
use Pim\Component\Catalog\Query\Filter\FieldFilterHelper;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Id filter for an Elasticsearch query
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdFilter extends AbstractFieldFilter
{
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
    public function addFieldFilter(
        $attribute,
        $operator,
        $value,
        $locale = null,
        $channel = null,
        $options = []
    ) {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        $this->checkValue($operator, $value);

        if (is_array($value)) {
            $value = array_map(
                function ($value) {
                    return (string)$value;
                },
                $value
            );
        } else {
            $value = (string)$value;
        }

        switch ($operator) {
            case Operators::EQUALS:
                $clause = [
                    'term' => [
                        'id' => $value
                    ]
                ];
                $this->searchQueryBuilder->addFilter($clause);
                break;
            case Operators::NOT_EQUAL:
                $mustNotClause = [
                    'term' => [
                        'id' => $value,
                    ],
                ];
                $filterClause = [
                    'exists' => [
                        'field' => 'id',
                    ],
                ];
                $this->searchQueryBuilder->addMustNot($mustNotClause);
                $this->searchQueryBuilder->addFilter($filterClause);
                break;
            case Operators::IN_LIST:
                $clause = [
                    'terms' => [
                        'id' => $value,
                    ],
                ];

                $this->searchQueryBuilder->addFilter($clause);
                break;
            case Operators::NOT_IN_LIST:
                $clause = [
                    'terms' => [
                        'id' => $value,
                    ],
                ];

                $this->searchQueryBuilder->addMustNot($clause);
                break;
            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }

        return $this;
    }

    /**
     * Checks that the value is a number.
     *
     * @param string $operator
     * @param mixed  $value
     */
    protected function checkValue($operator, $value)
    {
        if (in_array($operator, [Operators::EQUALS, Operators::NOT_EQUAL])) {
            FieldFilterHelper::checkString('id', $value, static::class);
        } else {
            FieldFilterHelper::checkArray('id', $value, static::class);
            foreach ($value as $oneValue) {
                if (!is_string($oneValue)) {
                    throw InvalidPropertyTypeException::validArrayStructureExpected(
                        'id',
                        'one of the value is not string',
                        static::class,
                        $value
                    );
                }
            }
        }
    }
}
