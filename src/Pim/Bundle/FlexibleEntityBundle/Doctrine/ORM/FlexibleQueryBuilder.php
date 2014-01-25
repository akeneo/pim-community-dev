<?php

namespace Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\FlexibleEntityBundle\Exception\FlexibleQueryException;
use Pim\Bundle\FlexibleEntityBundle\Doctrine\FlexibleQueryBuilderInterface;
use Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\Filter\BaseFilter;
use Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\Filter\EntityFilter;
use Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\Filter\MetricFilter;
use Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\Filter\PriceFilter;
use Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\Sorter\BaseSorter;
use Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\Sorter\EntitySorter;
use Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\Sorter\MetricSorter;

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
     * @return FlexibleQueryBuilder
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
     * @return FlexibleQueryBuilder
     */
    public function setScope($code)
    {
        $this->scope = $code;

        return $this;
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
    protected function getAllowedOperators($backendType)
    {
        $typeToOperator = array(
            AbstractAttributeType::BACKEND_TYPE_DATE     => array('=', '<', '<=', '>', '>='),
            AbstractAttributeType::BACKEND_TYPE_DATETIME => array('=', '<', '<=', '>', '>='),
            AbstractAttributeType::BACKEND_TYPE_DECIMAL  => array('=', '<', '<=', '>', '>='),
            AbstractAttributeType::BACKEND_TYPE_INTEGER  => array('=', '<', '<=', '>', '>='),
            AbstractAttributeType::BACKEND_TYPE_METRIC   => array('=', '<', '<=', '>', '>='),
            AbstractAttributeType::BACKEND_TYPE_BOOLEAN  => array('='),
            AbstractAttributeType::BACKEND_TYPE_OPTION   => array('IN', 'NOT IN'),
            AbstractAttributeType::BACKEND_TYPE_OPTIONS  => array('IN', 'NOT IN'),
            AbstractAttributeType::BACKEND_TYPE_TEXT     => array('=', 'NOT LIKE', 'LIKE'),
            AbstractAttributeType::BACKEND_TYPE_VARCHAR  => array('=', 'NOT LIKE', 'LIKE'),
            'prices'                                     => array('=', '<', '<=', '>', '>='),
        );

        if (!isset($typeToOperator[$backendType])) {
            throw new FlexibleQueryException('backend type '.$backendType.' is unknown');
        }

        return $typeToOperator[$backendType];
    }

    /**
     * TODO : should not be public !
     *
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
        $filter = new BaseFilter($this->qb, $this->locale, $this->scope);

        return $filter->prepareCriteriaCondition($field, $operator, $value);
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
        $backendType = $attribute->getBackendType();
        $allowed = $this->getAllowedOperators($backendType);

        $operators = is_array($operator) ? $operator : array($operator);
        foreach ($operators as $key) {
            if (!in_array($key, $allowed)) {
                throw new FlexibleQueryException(
                    sprintf('%s is not allowed for type %s, use %s', $key, $backendType, implode(', ', $allowed))
                );
            }
        }

        $options = ['pim_catalog_multiselect', 'pim_catalog_simpleselect'];
        $attributeType = $attribute->getAttributeType();
        if (isset($options[$attributeType])) {
            $filter = new EntityFilter($this->qb, $this->locale, $this->scope);

        } elseif ($attributeType === 'pim_catalog_price_collection') {
            $filter = new PriceFilter($this->qb, $this->locale, $this->scope);

        } elseif ($attributeType === 'pim_catalog_metric') {
            $filter = new MetricFilter($this->qb, $this->locale, $this->scope);

        } else {
            $filter = new BaseFilter($this->qb, $this->locale, $this->scope);
        }
        $filter->add($attribute, $operator, $value);

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
        $backendType = $attribute->getBackendType();

        if ($backendType === AbstractAttributeType::BACKEND_TYPE_OPTIONS
            || $backendType === AbstractAttributeType::BACKEND_TYPE_OPTION) {
            $sorter = new EntitySorter($this->qb, $this->locale, $this->scope);
        } elseif ($backendType === AbstractAttributeType::BACKEND_TYPE_METRIC) {
            $sorter = new MetricSorter($this->qb, $this->locale, $this->scope);
        } else {
            $sorter = new BaseSorter($this->qb, $this->locale, $this->scope);
        }

        $sorter->add($attribute, $direction);
    }
}
