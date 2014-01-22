<?php

namespace Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\FlexibleEntityBundle\Exception\FlexibleQueryException;

/**
 * Aims to customize a query builder to add useful shortcuts which allow to easily select, filter or sort a flexible
 * entity values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleQueryBuilder
{
    /**
     * QueryBuilder
     * @var QueryBuilder
     */
    protected $qb;

    /**
     * Locale code
     * @var string
     */
    protected $locale;

    /**
     * Scope code
     * @var string
     */
    protected $scope;

    /**
     * Alias counter, to avoid duplicate alias name
     * @return integer
     */
    protected $aliasCounter = 1;

    /**
     * Constructor
     * @param QueryBuilder $qb
     * @param string       $locale
     * @param string       $scope
     */
    public function __construct(QueryBuilder $qb, $locale, $scope)
    {
        $this->qb     = $qb;
        $this->locale = $locale;
        $this->scope  = $scope;
    }

    /**
     * Get query builder
     *
     * @return string
     */
    public function getQueryBuilder()
    {
        return $this->qb;
    }

    /**
     * Get locale code
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set locale code
     *
     * @param string $code
     *
     * @return FlexibleEntityRepository
     */
    public function setLocale($code)
    {
        $this->locale = $code;

        return $this;
    }

    /**
     * Get scope code
     *
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set scope code
     *
     * @param string $code
     *
     * @return FlexibleEntityRepository
     */
    public function setScope($code)
    {
        $this->scope = $code;

        return $this;
    }

    /**
     * Prepare join to attribute condition with current locale and scope criterias
     *
     * @param AbstractAttribute $attribute the attribute
     * @param string            $joinAlias the value join alias
     *
     * @throws FlexibleQueryException
     *
     * @return string
     */
    public function prepareAttributeJoinCondition(AbstractAttribute $attribute, $joinAlias)
    {
        $condition = $joinAlias.'.attribute = '.$attribute->getId();

        if ($attribute->isLocalizable()) {
            if ($this->getLocale() === null) {
                throw new FlexibleQueryException('Locale must be configured');
            }
            $condition .= ' AND '.$joinAlias.'.locale = '.$this->qb->expr()->literal($this->getLocale());
        }
        if ($attribute->isScopable()) {
            if ($this->getScope() === null) {
                throw new FlexibleQueryException('Scope must be configured');
            }
            $condition .= ' AND '.$joinAlias.'.scope = '.$this->qb->expr()->literal($this->getScope());
        }

        return $condition;
    }

    /**
     * Get allowed operators for related backend type
     *
     * @param string $backendType
     *
     * @throws FlexibleQueryException
     *
     * @return multitype:string
     */
    public function getAllowedOperators($backendType)
    {
        $typeToOperator = array(
            AbstractAttributeType::BACKEND_TYPE_DATE     => array('=', '<', '<=', '>', '>='),
            AbstractAttributeType::BACKEND_TYPE_DATETIME => array('=', '<', '<=', '>', '>='),
            AbstractAttributeType::BACKEND_TYPE_DECIMAL  => array('=', '<', '<=', '>', '>='),
            AbstractAttributeType::BACKEND_TYPE_INTEGER  => array('=', '<', '<=', '>', '>='),
            AbstractAttributeType::BACKEND_TYPE_BOOLEAN  => array('='),
            AbstractAttributeType::BACKEND_TYPE_OPTION   => array('IN', 'NOT IN'),
            AbstractAttributeType::BACKEND_TYPE_OPTIONS  => array('IN', 'NOT IN'),
            AbstractAttributeType::BACKEND_TYPE_TEXT     => array('=', 'NOT LIKE', 'LIKE'),
            AbstractAttributeType::BACKEND_TYPE_VARCHAR  => array('=', 'NOT LIKE', 'LIKE'),
        );

        if (!isset($typeToOperator[$backendType])) {
            throw new FlexibleQueryException('backend type '.$backendType.' is unknown');
        }

        return $typeToOperator[$backendType];
    }

    /**
     * Prepare criteria condition with field, operator and value
     *
     * @param string|array $field    the backend field name
     * @param string|array $operator the operator used to filter
     * @param string|array $value    the value(s) to filter
     *
     * @return string
     * @throws FlexibleQueryException
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
     * @throws FlexibleQueryException
     *
     * @return string
     */
    protected function prepareMultiCriteriaCondition($field, $operator, $value)
    {
        if (!is_array($value)) {
            throw new FlexibleQueryException('Values must be array');
        }

        if (!is_array($field)) {
            $fieldArray = array();
            foreach (array_keys($operator) as $key) {
                $fieldArray[$key] = $field;
            }
            $field = $fieldArray;
        }

        if (array_diff(array_keys($field), array_keys($operator))
            || array_diff(array_keys($field), array_keys($value))
        ) {
            throw new FlexibleQueryException('Field, operator and value arrays must have the same keys');
        }

        $conditions = array();
        foreach ($field as $key => $fieldName) {
            $conditions[] = $this->prepareSingleCriteriaCondition($fieldName, $operator[$key], $value[$key]);
        }

        return '(' . implode(' OR ', $conditions) . ')';
    }

    /**
     * Prepare single criteria condition with field, operator and value
     *
     * @param string $field    the backend field name
     * @param string $operator the operator used to filter
     * @param string $value    the value(s) to filter
     *
     * @throws FlexibleQueryException
     *
     * @return string
     */
    protected function prepareSingleCriteriaCondition($field, $operator, $value)
    {
        $operators = array('=' => 'eq', '<' => 'lt', '<=' => 'lte', '>' => 'gt', '>=' => 'gte', 'LIKE' => 'like');
        if (array_key_exists($operator, $operators)) {
            $method = $operators[$operator];

            return $this->qb->expr()->$method($field, $this->qb->expr()->literal($value))->__toString();
        }

        $operators = array('NULL' => 'isNull', 'NOT NULL' => 'isNotNull');
        if (array_key_exists($operator, $operators)) {
            $method = $operators[$operator];

            return $this->qb->expr()->$method($field);
        }

        $operators = array('IN' => 'in', 'NOT IN' => 'notIn');
        if (array_key_exists($operator, $operators)) {
            $method = $operators[$operator];

            return $this->qb->expr()->$method($field, $value)->__toString();
        }

        if ($operator == 'NOT LIKE') {
            return sprintf('%s NOT LIKE %s', $field, $this->qb->expr()->literal($value));
        }

        throw new FlexibleQueryException('operator '.$operator.' is not supported');
    }

    /**
     * Add an attribute to filter
     *
     * @param AbstractAttribute $attribute the attribute
     * @param string|array      $operator  the used operator
     * @param string|array      $value     the value(s) to filter
     *
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function addAttributeFilter(AbstractAttribute $attribute, $operator, $value)
    {
        $allowed = $this->getAllowedOperators($attribute->getBackendType());

        $operators = is_array($operator) ? $operator : array($operator);
        foreach ($operators as $key) {
            if (!in_array($key, $allowed)) {
                throw new FlexibleQueryException(
                    $key.' is not allowed for type '.$attribute->getBackendType().', use '.implode(', ', $allowed)
                );
            }
        }

        $joinAlias = 'filter'.$attribute->getCode().$this->aliasCounter++;

        if ($attribute->getBackendType() == AbstractAttributeType::BACKEND_TYPE_OPTIONS
            or $attribute->getBackendType() == AbstractAttributeType::BACKEND_TYPE_OPTION) {

            // inner join to value
            $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias);
            $this->qb->innerJoin(
                $this->qb->getRootAlias().'.' . $attribute->getBackendStorage(),
                $joinAlias,
                'WITH',
                $condition
            );

            // then join to option with filter on option id
            $joinAliasOpt = 'filterO'.$attribute->getCode().$this->aliasCounter;
            $backendField = sprintf('%s.%s', $joinAliasOpt, 'id');
            $condition = $this->prepareCriteriaCondition($backendField, $operator, $value);
            $this->qb->innerJoin($joinAlias.'.'.$attribute->getBackendType(), $joinAliasOpt, 'WITH', $condition);

        } elseif ($attribute->getBackendType() == AbstractAttributeType::BACKEND_TYPE_ENTITY) {

            // inner join to value
            $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias);
            $rootAlias = $this->qb->getRootAliases();
            $this->qb->innerJoin($rootAlias[0] .'.'. $attribute->getBackendStorage(), $joinAlias, 'WITH', $condition);

            // then join to linked entity with filter on id
            $joinAliasOpt = 'filterentity'.$attribute->getCode().$this->aliasCounter;
            $backendField = sprintf('%s.id', $joinAliasEntity);
            $condition = $this->prepareCriteriaCondition($backendField, $operator, $value);
            $this->qb->innerJoin($joinAlias .'.'. $attribute->getBackendType(), $joinAliasEntity, 'WITH', $condition);

        } else {

            // inner join with condition on backend value
            $backendField = sprintf('%s.%s', $joinAlias, $attribute->getBackendType());
            $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias);
            $condition .= ' AND '.$this->prepareCriteriaCondition($backendField, $operator, $value);
            $this->qb->innerJoin(
                $this->qb->getRootAlias().'.'.$attribute->getBackendStorage(),
                $joinAlias,
                'WITH',
                $condition
            );
        }

        return $this;
    }

    /**
     * Sort by attribute value
     *
     * @param AbstractAttribute $attribute the attribute to sort on
     * @param string            $direction the direction to use
     */
    public function addAttributeOrderBy(AbstractAttribute $attribute, $direction)
    {
        $aliasPrefix = 'sorter';
        $joinAlias   = $aliasPrefix.'V'.$attribute->getCode().$this->aliasCounter++;

        if ($attribute->getBackendType() == AbstractAttributeType::BACKEND_TYPE_OPTIONS
            or $attribute->getBackendType() == AbstractAttributeType::BACKEND_TYPE_OPTION) {

            // join to value
            $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias);
            $this->qb->leftJoin(
                $this->qb->getRootAlias().'.' . $attribute->getBackendStorage(),
                $joinAlias,
                'WITH',
                $condition
            );

            // then to option and option value to sort on
            $joinAliasOpt = $aliasPrefix.'O'.$attribute->getCode().$this->aliasCounter;
            $condition    = $joinAliasOpt.".attribute = ".$attribute->getId();
            $this->qb->leftJoin($joinAlias.'.'.$attribute->getBackendType(), $joinAliasOpt, 'WITH', $condition);

            $joinAliasOptVal = $aliasPrefix.'OV'.$attribute->getCode().$this->aliasCounter;
            $condition       = $joinAliasOptVal.'.locale = '.$this->qb->expr()->literal($this->getLocale());
            $this->qb->leftJoin($joinAliasOpt.'.optionValues', $joinAliasOptVal, 'WITH', $condition);

            $this->qb->addOrderBy($joinAliasOpt.'.code', $direction);
            $this->qb->addOrderBy($joinAliasOptVal.'.value', $direction);

        } elseif ($attribute->getBackendType() === AbstractAttributeType::BACKEND_TYPE_METRIC) {

            // join to value
            $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias);
            $this->qb->leftJoin(
                $this->qb->getRootAlias().'.' . $attribute->getBackendStorage(),
                $joinAlias,
                'WITH',
                $condition
            );

            $joinAliasMetric = $aliasPrefix.'M'.$attribute->getCode().$this->aliasCounter;
            $this->qb->leftJoin($joinAlias.'.'.$attribute->getBackendType(), $joinAliasMetric);

            $this->qb->addOrderBy($joinAliasMetric.'.baseData', $direction);

        } else {
            // join to value and sort on
            $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias);

            // Remove current join in order to put the orderBy related join
            // at first place in the join queue for performances reasons
            $joinsSet = $this->qb->getDQLPart('join');
            $this->qb->resetDQLPart('join');

            $this->qb->leftJoin(
                $this->qb->getRootAlias().'.'.$attribute->getBackendStorage(),
                $joinAlias,
                'WITH',
                $condition
            );
            $this->qb->addOrderBy($joinAlias.'.'.$attribute->getBackendType(), $direction);

            // Reapply previous join after the orderBy related join
            $this->applyJoins($joinsSet);

        }
    }

    /**
     * Reapply joins from a set of joins got from getDQLPart('join')
     *
     * @param array $joinsSet
     */
    protected function applyJoins($joinsSet)
    {
        foreach ($joinsSet as $joins) {
            foreach ($joins as $join) {
                if ($join->getJoinType() === Join::LEFT_JOIN) {
                    $this->qb->leftJoin(
                        $join->getJoin(),
                        $join->getAlias(),
                        $join->getConditionType(),
                        $join->getCondition(),
                        $join->getIndexBy()
                    );
                } else {
                    $this->qb->join(
                        $join->getJoin(),
                        $join->getAlias(),
                        $join->getConditionType(),
                        $join->getCondition(),
                        $join->getIndexBy()
                    );
                }
            }
        }
    }
}
