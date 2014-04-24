<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Exception\ProductQueryException;
use Pim\Bundle\CatalogBundle\Doctrine\ProductQueryBuilderInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\BaseFilter;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

/**
 * Aims to customize a query builder to add useful shortcuts which allow to easily select, filter or sort a product
 * values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductQueryBuilder implements ProductQueryBuilderInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /** @var CatalogContext */
    protected $context;

    /**
     * Constructor
     *
     * @param CatalogContext $catalogContext
     */
    public function __construct(CatalogContext $catalogContext)
    {
        $this->context = $catalogContext;
    }

    /**
     * Get query builder
     *
     * @param QueryBuilder $qb
     *
     * @return ProductQueryBuilder
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
        if (!$this->qb) {
            throw new \LogicException('Query builder must be configured');
        }

        return $this->qb;
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
                throw new ProductQueryException(
                    sprintf('%s is not allowed for type %s, use %s', $key, $attributeType, implode(', ', $allowed))
                );
            }
        }

        $customFilters = [
            'pim_catalog_multiselect'      => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\EntityFilter',
            'pim_catalog_simpleselect'     => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\EntityFilter',
            'pim_catalog_metric'           => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\MetricFilter',
            'pim_catalog_price_collection' => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\PriceFilter'
        ];

        if (isset($customFilters[$attributeType])) {
            $filterClass = $customFilters[$attributeType];
        } else {
            $filterClass = 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\BaseFilter';
        }

        // TODO : add a CatalogContextAware interface to avoid to inject context everywhere ?
        $filter = new $filterClass($this->getQueryBuilder(), $this->context);
        $filter->addAttributeFilter($attribute, $operator, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value)
    {
        $customFilters = [
            'family'       => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\EntityFilter',
            'groups'       => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\EntityFilter',
            'completeness' => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\CompletenessFilter'
        ];
        if (isset($customFilters[$field])) {
            $filterClass = $customFilters[$field];
        } else {
            $filterClass = 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\BaseFilter';
        }

        $filter = new $filterClass($this->getQueryBuilder(), $this->context);
        $filter->addFieldFilter($field, $operator, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeSorter(AbstractAttribute $attribute, $direction)
    {
        $attributeType = $attribute->getAttributeType();
        $customSorters = [
            'pim_catalog_multiselect'  => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\EntitySorter',
            'pim_catalog_simpleselect' => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\EntitySorter',
            'pim_catalog_metric'       => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\MetricSorter'
        ];

        if (isset($customSorters[$attributeType])) {
            $sorterClass = $customSorters[$attributeType];
        } else {
            $sorterClass = 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\BaseSorter';
        }

        $sorter = new $sorterClass($this->getQueryBuilder(), $this->context);
        $sorter->addAttributeSorter($attribute, $direction);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldSorter($field, $direction)
    {
        $field = (strpos($field, 'in_group_') !== false) ? 'in_group' : $field;
        $customSorters = [
            'family'       => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\FamilySorter',
            'completeness' => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\CompletenessSorter',
            'in_group'     => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\InGroupSorter'
        ];

        if (isset($customSorters[$field])) {
            $sorterClass = $customSorters[$field];
        } else {
            $sorterClass = 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\BaseSorter';
        }

        $sorter = new $sorterClass($this->getQueryBuilder(), $this->context);
        $sorter->addFieldSorter($field, $direction);

        return $this;
    }

    /**
     * Get allowed operators for related attribute
     *
     * @param AbstractAttribute $attribute
     *
     * @throws ProductQueryException
     *
     * @return array
     */
    protected function getAllowedOperators($attribute)
    {
        $operators = [
            'pim_catalog_identifier'       => ['=', 'NOT LIKE', 'LIKE'],
            'pim_catalog_text'             => ['=', 'NOT LIKE', 'LIKE', 'EMPTY'],
            'pim_catalog_textarea'         => ['=', 'NOT LIKE', 'LIKE', 'EMPTY'],
            'pim_catalog_simpleselect'     => ['IN', 'NOT IN'],
            'pim_catalog_multiselect'      => ['IN', 'NOT IN'],
            'pim_catalog_number'           => ['=', '<', '<=', '>', '>=', 'EMPTY'],
            'pim_catalog_boolean'          => ['='],
            'pim_catalog_date'             => ['=', '<', '<=', '>', '>=', 'BETWEEN', 'EMPTY'],
            'pim_catalog_price_collection' => ['=', '<', '<=', '>', '>='],
            'pim_catalog_metric'           => ['=', '<', '<=', '>', '>=']
        ];

        $attributeType = $attribute->getAttributeType();
        if (!isset($operators[$attributeType])) {
            throw new \LogicException(
                sprintf('Attribute type %s is not configured for attribute %s', $attributeType, $attribute->getCode())
            );
        }

        return $operators[$attributeType];
    }
}
