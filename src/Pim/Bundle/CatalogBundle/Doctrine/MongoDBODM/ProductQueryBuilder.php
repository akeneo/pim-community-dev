<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Doctrine\ProductQueryBuilderInterface;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

/**
 * Aims to customize a query builder to add useful shortcuts which allow to easily select, filter or sort a flexible
 * entity values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
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
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->qb;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(AbstractAttribute $attribute, $operator, $value)
    {
        $attributeType = $attribute->getAttributeType();
        $customFilters = [
            'pim_catalog_multiselect'      => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\EntityFilter',
            'pim_catalog_simpleselect'     => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\EntityFilter',
            'pim_catalog_metric'           => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\MetricFilter',
            'pim_catalog_price_collection' => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\PriceFilter',
            'pim_catalog_date'             => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\DateFilter'
        ];

        if (isset($customFilters[$attributeType])) {
            $filterClass = $customFilters[$attributeType];
        } else {
            $filterClass = 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\BaseFilter';
        }

        $filter = new $filterClass($this->qb, $this->context);
        $filter->addAttributeFilter($attribute, $operator, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value)
    {
        $customFilters = [
            'created'       => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\DateFilter',
            'updated'       => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\DateFilter',
            'family'        => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\EntityFilter',
            'groupIds'        => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\EntityFilter',
            'completeness'  => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\CompletenessFilter'
        ];

        if (isset($customFilters[$field])) {
            $filterClass = $customFilters[$field];
        } else {
            $filterClass = 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\BaseFilter';
        }

        $filter = new $filterClass($this->qb, $this->context);
        $filter->addFieldFilter($field, $operator, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeSorter(AbstractAttribute $attribute, $direction)
    {
        $attributeType = $attribute->getAttributeType();
        $customSorters = [];

        if (isset($customSorters[$attributeType])) {
            $sorterClass = $customSorters[$attributeType];
        } else {
            $sorterClass = 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter\BaseSorter';
        }

        $sorter = new $sorterClass($this->qb, $this->context);
        $sorter->addAttributeSorter($attribute, $direction);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldSorter($field, $direction)
    {
        $customSorters = [
            'family'       => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter\FamilySorter',
            'completeness' => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter\CompletenessSorter',
        ];

        if (isset($customSorters[$field])) {
            $sorterClass = $customSorters[$field];
        } elseif (strpos($field, 'in_group_') !== false) {
            $sorterClass = 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter\InGroupSorter';
        } else {
            $sorterClass = 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter\BaseSorter';
        }

        $sorter = new $sorterClass($this->qb, $this->context);
        $sorter->addFieldSorter($field, $direction);

        return $this;
    }
}
