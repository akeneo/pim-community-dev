<?php

namespace Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\Exception\FlexibleQueryException;
use Pim\Bundle\FlexibleEntityBundle\Doctrine\FlexibleQueryBuilderInterface;
use Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\Filter\BaseFilter;

/**
 * Aims to customize a query builder to add useful shortcuts which allow to easily select, filter or sort a flexible
 * entity values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleQueryBuilder implements FlexibleQueryBuilderInterface
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
     * Get query builder
     *
     * @param QueryBuilder $qb
     *
     * @return FlexibleQueryBuilder
     */
    public function setQueryBuilder(QueryBuilder $qb)
    {
        $this->qb = $qb;

        return $this;
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
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($code)
    {
        $this->locale = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * {@inheritdoc}
     */
    public function setScope($code)
    {
        $this->scope = $code;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(AbstractAttribute $attribute, $operator, $value)
    {
        $attributeType = $attribute->getAttributeType();
        $allowed = $this->getAllowedOperators($attribute);
        $operators = is_array($operator) ? $operator : array($operator);
        foreach ($operators as $key) {
            if (!in_array($key, $allowed)) {
                throw new FlexibleQueryException(
                    sprintf('%s is not allowed for type %s, use %s', $key, $attributeType, implode(', ', $allowed))
                );
            }
        }

        $customFilters = [
            'pim_catalog_multiselect'      => 'Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\Filter\EntityFilter',
            'pim_catalog_simpleselect'     => 'Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\Filter\EntityFilter',
            'pim_catalog_metric'           => 'Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\Filter\MetricFilter',
            'pim_catalog_price_collection' => 'Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\Filter\PriceFilter'
        ];

        if (isset($customFilters[$attributeType])) {
            $filterClass = $customFilters[$attributeType];
        } else {
            $filterClass = 'Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\Filter\BaseFilter';
        }

        $filter = new $filterClass($this->qb, $this->locale, $this->scope);
        $filter->add($attribute, $operator, $value);

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
     * {@inheritdoc}
     */
    public function addAttributeSorter(AbstractAttribute $attribute, $direction)
    {
        $attributeType = $attribute->getAttributeType();
        $customSorters = [
            'pim_catalog_multiselect'  => 'Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\Sorter\EntitySorter',
            'pim_catalog_simpleselect' => 'Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\Sorter\EntitySorter',
            'pim_catalog_metric'       => 'Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\Sorter\MetricSorter'
        ];

        if (isset($customSorters[$attributeType])) {
            $sorterClass = $customSorters[$attributeType];
        } else {
            $sorterClass = 'Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\Sorter\BaseSorter';
        }

        $sorter = new $sorterClass($this->qb, $this->locale, $this->scope);
        $sorter->add($attribute, $direction);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldSorter($field, $direction)
    {
        $field = current($this->qb->getRootAliases()).'.'.$field;
        $this->qb->addOrderBy($field, $direction);

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
     * @throws FlexibleQueryException
     */
    protected function prepareCriteriaCondition($field, $operator, $value)
    {
        $filter = new BaseFilter($this->qb, $this->locale, $this->scope);

        return $filter->prepareCriteriaCondition($field, $operator, $value);
    }

    /**
     * Get allowed operators for related attribute
     *
     * @param AbstractAttribute $attribute
     *
     * @throws FlexibleQueryException
     *
     * @return array
     */
    protected function getAllowedOperators($attribute)
    {
        $operators = [
            'pim_catalog_identifier'       => ['=', 'NOT LIKE', 'LIKE'],
            'pim_catalog_text'             => ['=', 'NOT LIKE', 'LIKE'],
            'pim_catalog_textarea'         => ['=', 'NOT LIKE', 'LIKE'],
            'pim_catalog_simpleselect'     => ['IN', 'NOT IN'],
            'pim_catalog_multiselect'      => ['IN', 'NOT IN'],
            'pim_catalog_number'           => ['=', '<', '<=', '>', '>='],
            'pim_catalog_boolean'          => ['='],
            'pim_catalog_date'             => ['=', '<', '<=', '>', '>=', 'BETWEEN'],
            'pim_catalog_price_collection' => ['=', '<', '<=', '>', '>='],
            'pim_catalog_metric'           => ['=', '<', '<=', '>', '>=']
        ];

        $attributeType = $attribute->getAttributeType();
        if (!isset($operators[$attributeType])) {
            throw new \LogicalException(
                sprintf('Attribute type %s is not configured for attribute %s', $attributeType, $attribute->getCode())
            );
        }

        return $operators[$attributeType];
    }
}
