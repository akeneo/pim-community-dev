<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Exception\ProductQueryException;
use Pim\Bundle\CatalogBundle\Doctrine\ProductQueryBuilderInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\BaseFilter;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Doctrine\FilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\AttributeFilterInterface;

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

    /** @var array */
    protected $attributeFilters = [];

    /** @var array of priorized field filters */
    protected $fieldFilters = [];

    /** @var array */
    protected $attributeSorters = [
        'pim_catalog_multiselect'  => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\EntitySorter',
        'pim_catalog_simpleselect' => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\EntitySorter',
        'pim_catalog_metric'       => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\MetricSorter'
    ];

    /** @var array */
    protected $fieldSorters = [
        'family'       => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\FamilySorter',
        'completeness' => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\CompletenessSorter',
        'in_group'     => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\InGroupSorter',
        'is_associated' => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\IsAssociatedSorter',
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
                'Attribute "%s" with operator "%s" is not supported',
                $attribute->getCode(),
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
        $attributeType = $attribute->getAttributeType();
        if (isset($this->attributeSorters[$attributeType])) {
            $sorterClass = $this->attributeSorters[$attributeType];
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

        if (isset($this->fieldSorters[$field])) {
            $sorterClass = $this->fieldSorters[$field];
        } else {
            $sorterClass = 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\BaseSorter';
        }

        $sorter = new $sorterClass($this->getQueryBuilder(), $this->context);
        $sorter->addFieldSorter($field, $direction);

        return $this;
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
}
