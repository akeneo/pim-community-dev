<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Doctrine\ProductQueryBuilderInterface;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

/**
 * Aims to customize a query builder to add useful shortcuts which allow to easily select, filter or sort a product
 * values
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

    /** @var array */
    protected $attributeFilters = [
        'pim_catalog_multiselect'      => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\EntityFilter',
        'pim_catalog_simpleselect'     => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\EntityFilter',
        'pim_catalog_metric'           => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\MetricFilter',
        'pim_catalog_price_collection' => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\PriceFilter',
        'pim_catalog_date'             => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\DateFilter'
    ];

    /** @var array */
    protected $fieldFilters = [
        'id'            => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\ProductIdFilter',
        'created'       => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\DateFilter',
        'updated'       => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\DateFilter',
        'family'        => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\EntityFilter',
        'groups'        => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\GroupsFilter',
        'completeness'  => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\CompletenessFilter'
    ];

    /** @var array */
    protected $attributeSorters = [];

    /** @var array */
    protected $fieldSorters = [
        'family'       => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter\FamilySorter',
        'completeness' => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter\CompletenessSorter',
    ];

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
        if (isset($this->attributeFilters[$attributeType])) {
            $filterClass = $this->attributeFilters[$attributeType];
        } else {
            $filterClass = 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\BaseFilter';
        }

        $filter = new $filterClass($this->getQueryBuilder(), $this->context);
        $filter->addAttributeFilter($attribute, $operator, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value)
    {
        if (isset($this->fieldFilters[$field])) {
            $filterClass = $this->fieldFilters[$field];
        } else {
            $filterClass = 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\BaseFilter';
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
        if (isset($this->attributeSorters[$attributeType])) {
            $sorterClass = $this->attributeSorters[$attributeType];
        } else {
            $sorterClass = 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter\BaseSorter';
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
        if (isset($this->fieldSorters[$field])) {
            $sorterClass = $this->fieldSorters[$field];
        } elseif (strpos($field, 'in_group_') !== false) {
            $sorterClass = 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter\InGroupSorter';
        } else {
            $sorterClass = 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Sorter\BaseSorter';
        }

        $sorter = new $sorterClass($this->getQueryBuilder(), $this->context);
        $sorter->addFieldSorter($field, $direction);

        return $this;
    }
}
