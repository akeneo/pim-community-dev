<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
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
        // TODO : implement custom filters
        $customFilters = [
            'pim_catalog_multiselect'      => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\EntityFilter',
            'pim_catalog_simpleselect'     => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\EntityFilter',
            'pim_catalog_price_collection' => 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\PriceFilter'
        ];

        if (isset($customFilters[$attributeType])) {
            $filterClass = $customFilters[$attributeType];
        } else {
            $filterClass = 'Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter\BaseFilter';
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
        throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
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
        $sorter->add($attribute, $direction);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldSorter($field, $direction)
    {
        throw new \RuntimeException("Not implemented yet ! ".__CLASS__."::".__METHOD__);
    }
}
