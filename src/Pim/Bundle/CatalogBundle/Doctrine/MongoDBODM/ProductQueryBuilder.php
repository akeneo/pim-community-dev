<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Doctrine\ProductQueryBuilderInterface;

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

        $filter = new $filterClass($this->qb, $this->locale, $this->scope);
        $filter->addAttributeFilter($attribute, $operator, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value)
    {
        $customFilters = [
            'created' => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\DateFilter',
            'updated' => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\DateFilter'
        ];

        if (isset($customFilters[$field])) {
            $filterClass = $customFilters[$field];
        } else {
            $filterClass = 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\BaseFilter';
        }

        $filter = new $filterClass($this->qb, $this->locale, $this->scope);
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

        $sorter = new $sorterClass($this->qb, $this->locale, $this->scope);
        $sorter->addAttributeSorter($attribute, $direction);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldSorter($field, $direction)
    {
        $this->qb->sort(ProductQueryUtility::NORMALIZED_FIELD.'.'.$field, $direction);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFamilySorter($direction)
    {
        $field = sprintf("%s.family.label.%s", ProductQueryUtility::NORMALIZED_FIELD, $this->getLocale());
        $this->qb->sort($field, $direction);
    }

    /**
     * {@inheritdoc}
     */
    public function addCompletenessSorter($direction)
    {
        $field = sprintf(
            "%s.completenesses.%s-%s",
            ProductQueryUtility::NORMALIZED_FIELD,
            $this->getScope(),
            $this->getLocale()
        );
        $this->qb->sort($field, $direction);
    }
}
