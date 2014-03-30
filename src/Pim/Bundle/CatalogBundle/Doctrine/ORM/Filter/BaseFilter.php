<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Exception\ProductQueryException;
use Pim\Bundle\CatalogBundle\Doctrine\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\ValueJoin;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

/**
 * Base filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseFilter implements AttributeFilterInterface, FieldFilterInterface
{
    /**
     * @var QueryBuilder
     */
    protected $qb;

    /** @var CatalogContext */
    protected $context;

    /**
     * Alias counter, to avoid duplicate alias name
     * @return integer
     */
    protected $aliasCounter = 1;

    /**
     * Instanciate a sorter
     *
     * @param QueryBuilder   $qb
     * @param CatalogContext $context
     */
    public function __construct(QueryBuilder $qb, CatalogContext $context)
    {
        $this->qb      = $qb;
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(AbstractAttribute $attribute, $operator, $value)
    {
        $backendType = $attribute->getBackendType();
        $joinAlias = 'filter'.$attribute->getCode().$this->aliasCounter++;

        $backendField = sprintf('%s.%s', $joinAlias, $backendType);
        $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias);
        $condition .= ' AND '.$this->prepareCriteriaCondition($backendField, $operator, $value);
        $this->qb->innerJoin(
            $this->qb->getRootAlias().'.'.$attribute->getBackendStorage(),
            $joinAlias,
            'WITH',
            $condition
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value)
    {
        $field = current($this->qb->getRootAliases()).'.'.$field;
        $condition = $this->prepareCriteriaCondition($field, $operator, $value);
        $this->qb->andWhere($condition);

        return $this;
    }

    /**
     * Prepare criteria condition with field, operator and value
     *
     * @param string|array $field    the backend field name
     * @param string|array $operator the operator used to filter
     * @param string|array $value    the value(s) to filter
     *
     * @return string
     * @throws ProductQueryException
     */
    protected function prepareCriteriaCondition($field, $operator, $value)
    {
        if (is_array($operator)) {
            return $this->prepareMultiCriteriaCondition($field, $operator, $value);

        } else {
            return $this->prepareSingleCriteriaCondition($field, $operator, $value);
        }
    }

    /**
     * Prepare join to attribute condition with current locale and scope criterias
     *
     * @param AbstractAttribute $attribute the attribute
     * @param string            $joinAlias the value join alias
     *
     * @throws ProductQueryException
     *
     * @return string
     */
    protected function prepareAttributeJoinCondition(AbstractAttribute $attribute, $joinAlias)
    {
        $joinHelper = new ValueJoin($this->qb, $this->context);

        return $joinHelper->prepareCondition($attribute, $joinAlias);
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
            $fieldArray = array();
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
     * @throws ProductQueryException
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

        if ($operator == 'BETWEEN') {
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
