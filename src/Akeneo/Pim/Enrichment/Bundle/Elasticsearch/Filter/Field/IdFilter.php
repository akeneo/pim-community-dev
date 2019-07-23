<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterHelper;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

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
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @param array $supportedFields
     * @param array $supportedOperators
     * @param string $prefix
     */
    public function __construct(
        array $supportedFields = [],
        array $supportedOperators = [],
        string $prefix
    ) {
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
        $this->fieldName = $supportedFields[0];
        $this->prefix = $prefix;
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
                    return (string) $this->prefix.$value;
                },
                $value
            );
        } else {
            $value = (string)$this->prefix.$value;
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
