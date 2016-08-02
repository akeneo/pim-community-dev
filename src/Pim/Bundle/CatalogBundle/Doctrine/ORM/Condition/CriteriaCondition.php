<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Condition;

use Doctrine\ORM\QueryBuilder;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use Pim\Component\Catalog\Exception\ProductQueryException;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Criteria condition utils
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CriteriaCondition
{
    /**
     * @var QueryBuilder
     */
    protected $qb;

    /**
     * @param QueryBuilder $qb
     */
    public function __construct(QueryBuilder $qb)
    {
        $this->qb = $qb;
    }

    /**
     * Prepare criteria condition with field, operator and value
     *
     * @param string|array $field    the backend field name
     * @param string|array $operator the operator used to filter
     * @param string|array $value    the value(s) to filter
     *
     * @throws ProductQueryException
     *
     * @return string
     */
    public function prepareCriteriaCondition($field, $operator, $value)
    {
        if (is_array($operator)) {
            return $this->prepareMultiCriteriaCondition($field, $operator, $value);
        } else {
            return $this->prepareSingleCriteriaCondition($field, $operator, $value);
        }
    }

    /**
     * Prepare multi criteria condition with field, operator and value
     *
     * @param array $field    the backend field name
     * @param array $operator the operator used to filter
     * @param array $value    the value(s) to filter
     *
     * @throws ProductQueryException
     *
     * @return string
     */
    protected function prepareMultiCriteriaCondition($field, $operator, $value)
    {
        if (!is_array($value)) {
            throw new ProductQueryException('Values must be array');
        }

        if (!is_array($field)) {
            $fieldArray = [];
            foreach (array_keys($operator) as $key) {
                $fieldArray[$key] = $field;
            }
            $field = $fieldArray;
        }

        if (array_diff(array_keys($field), array_keys($operator))
            || array_diff(array_keys($field), array_keys($value))
        ) {
            throw new ProductQueryException('Field, operator and value arrays must have the same keys');
        }

        $conditions = [];
        foreach ($field as $key => $fieldName) {
            $conditions[] = $this->prepareSingleCriteriaCondition($fieldName, $operator[$key], $value[$key]);
        }

        return '(' . implode(' OR ', $conditions) . ')';
    }

    /**
     * Prepare single criteria condition with field, operator and value
     *
     * @param string       $field    the backend field name
     * @param string       $operator the operator used to filter
     * @param string|array $value    the value(s) to filter
     *
     * @throws ProductQueryException
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    protected function prepareSingleCriteriaCondition($field, $operator, $value)
    {
        $operators = [
            Operators::EQUALS                => 'eq',
            Operators::NOT_EQUAL             => 'neq',
            Operators::LOWER_THAN            => 'lt',
            Operators::LOWER_OR_EQUAL_THAN   => 'lte',
            Operators::GREATER_THAN          => 'gt',
            Operators::GREATER_OR_EQUAL_THAN => 'gte',
            Operators::IS_LIKE               => 'like',
            Operators::NOT_LIKE              => 'notLike'
        ];
        if (array_key_exists($operator, $operators)) {
            if (!is_scalar($value)) {
                throw new \InvalidArgumentException(
                    sprintf('Only scalar values are allowed for operators %s.', implode(', ', $operators))
                );
            }
            $method = $operators[$operator];
            $condition = $this->qb->expr()->$method($field, $this->qb->expr()->literal($value));

            return is_object($condition) ? $condition->__toString() : $condition;
        }

        $operators = [
            Operators::IS_NULL      => 'isNull',
            Operators::IS_NOT_NULL  => 'isNotNull',
            Operators::IS_EMPTY     => 'isNull',
            Operators::IS_NOT_EMPTY => 'isNotNull'
        ];
        if (array_key_exists($operator, $operators)) {
            $method = $operators[$operator];

            return $this->qb->expr()->$method($field);
        }

        $operators = [Operators::IN_LIST => 'in', Operators::NOT_IN_LIST => 'notIn'];
        if (array_key_exists($operator, $operators)) {
            if (!is_array($value)) {
                throw new \InvalidArgumentException(
                    sprintf('Only array values are allowed for operators %s.', implode(', ', $operators))
                );
            }

            if (0 === count($value)) {
                throw InvalidArgumentException::emptyArray($field);
            }

            $method = $operators[$operator];

            return $this->qb->expr()->$method($field, $value)->__toString();
        }

        if (Operators::BETWEEN === $operator) {
            if (!is_array($value)) {
                throw new \InvalidArgumentException(sprintf('Only array values are allowed for operator BETWEEN'));
            }

            return sprintf(
                '%s BETWEEN %s AND %s',
                $field,
                $this->qb->expr()->literal($value[0]),
                $this->qb->expr()->literal($value[1])
            );
        }

        throw new ProductQueryException('operator '.$operator.' is not supported');
    }
}
