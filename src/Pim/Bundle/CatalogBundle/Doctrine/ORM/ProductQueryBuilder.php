<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Doctrine\ProductQueryBuilderInterface;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Doctrine\FilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\SorterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\FieldSorterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\AttributeSorterInterface;

/**
 * Builds a product query builder by using  shortcuts to easily select, filter or sort products
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

    /** @var AttributeFilterInterface[] priorized attribute filters */
    protected $attributeFilters = [];

    /** @var FieldSorterInterface[] priorized field filters */
    protected $fieldFilters = [];

    /** @var AttributeSorterInterface[] priorized attribute sorters */
    protected $attributeSorters = [];

    /** @var FieldSorterInterface[] priorized field sorters */
    protected $fieldSorters = [];

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
        ksort($this->attributeFilters);
        foreach ($this->attributeFilters as $filters) {
            foreach ($filters as $filter) {
                if ($filter->supportsAttribute($attribute) && $filter->supportsOperator($operator)) {
                    $filter->setQueryBuilder($this->getQueryBuilder());
                    $filter->addAttributeFilter($attribute, $operator, $value);

                    return $this;
                }
            }
        }

        throw new \LogicException(
            sprintf(
                'Attribute "%s" (%s) with operator "%s" is not supported',
                $attribute->getCode(),
                $attribute->getAttributeType(),
                $operator
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value)
    {
        ksort($this->fieldFilters);
        foreach ($this->fieldFilters as $filters) {
            foreach ($filters as $filter) {
                if ($filter->supportsField($field) && $filter->supportsOperator($operator)) {
                    $filter->setQueryBuilder($this->getQueryBuilder());
                    $filter->addFieldFilter($field, $operator, $value);

                    return $this;
                }
            }
        }

        throw new \LogicException(sprintf('Field "%s" with operator "%s" is not supported', $field, $operator));
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeSorter(AbstractAttribute $attribute, $direction)
    {
        ksort($this->attributeSorters);
        foreach ($this->attributeSorters as $sorters) {
            foreach ($sorters as $sorter) {
                if ($sorter->supportsAttribute($attribute)) {
                    $sorter->setQueryBuilder($this->getQueryBuilder());
                    $sorter->addAttributeSorter($attribute, $direction);

                    return $this;
                }
            }
        }

        throw new \LogicException(
            sprintf(
                'Attribute "%s" (%s) is not supported',
                $attribute->getCode(),
                $attribute->getAttributeType()
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldSorter($field, $direction)
    {
        ksort($this->fieldSorters);
        foreach ($this->fieldSorters as $sorters) {
            foreach ($sorters as $sorter) {
                if ($sorter->supportsField($field)) {
                    $sorter->setQueryBuilder($this->getQueryBuilder());
                    $sorter->addFieldSorter($field, $direction);

                    return $this;
                }
            }
        }

        throw new \LogicException(sprintf('Field "%s" is not supported', $field));
    }

    /**
     * Register the filter
     *
     * @param FilterInterface $filter
     * @param integer         $priority
     */
    public function registerFilter(FilterInterface $filter, $priority)
    {
        if ($filter instanceof FieldFilterInterface) {
            if (!isset($this->fieldFilters[$priority])) {
                $this->fieldFilters[$priority]= [];
            }
            $this->fieldFilters[$priority][]= $filter;
        }
        if ($filter instanceof AttributeFilterInterface) {
            if (!isset($this->attributeFilters[$priority])) {
                $this->attributeFilters[$priority]= [];
            }
            $this->attributeFilters[$priority][]= $filter;
        }
    }

    /**
     * Register the sorter
     *
     * @param SorterInterface $sorter
     * @param integer         $priority
     */
    public function registerSorter(SorterInterface $sorter, $priority)
    {
        if ($sorter instanceof FieldSorterInterface) {
            if (!isset($this->fieldSorters[$priority])) {
                $this->fieldSorters[$priority]= [];
            }
            $this->fieldSorters[$priority][]= $sorter;
        }
        if ($sorter instanceof AttributeSorterInterface) {
            if (!isset($this->attributeSorters[$priority])) {
                $this->attributeSorters[$priority]= [];
            }
            $this->attributeSorters[$priority][]= $sorter;
        }
    }
}
