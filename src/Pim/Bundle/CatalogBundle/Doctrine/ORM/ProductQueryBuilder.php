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

    /** @var array */
    protected $attributeFilters = [
        'pim_catalog_multiselect'      => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\OptionsFilter',
        'pim_catalog_simpleselect'     => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\OptionFilter',
        'pim_catalog_metric'           => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\MetricFilter',
        'pim_catalog_price_collection' => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\PriceFilter'
    ];

    /** @var array */
    protected $fieldFilters = [
        'family'       => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\EntityFilter',
        'groups'       => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\EntityFilter',
        'completeness' => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\CompletenessFilter',
        'created'      => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\DateFilter',
        'updated'      => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter\DateFilter',
    ];

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
        'in_group'     => 'Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter\InGroupSorter'
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

        if (isset($this->attributeFilters[$attributeType])) {
            $filterClass = $this->attributeFilters[$attributeType];
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
        if (isset($this->fieldFilters[$field])) {
            $filterClass = $this->fieldFilters[$field];
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
            'pim_catalog_identifier'       => ['=', 'NOT LIKE', 'LIKE', 'IN'],
            'pim_catalog_text'             => ['=', 'NOT LIKE', 'LIKE', 'EMPTY'],
            'pim_catalog_textarea'         => ['=', 'NOT LIKE', 'LIKE', 'EMPTY'],
            'pim_catalog_simpleselect'     => ['IN', 'NOT IN'],
            'pim_catalog_multiselect'      => ['IN', 'NOT IN'],
            'pim_catalog_number'           => ['=', '<', '<=', '>', '>=', 'EMPTY'],
            'pim_catalog_boolean'          => ['='],
            'pim_catalog_date'             => ['=', '<', '<=', '>', '>=', 'BETWEEN', 'EMPTY'],
            'pim_catalog_price_collection' => ['=', '<', '<=', '>', '>=', 'EMPTY'],
            'pim_catalog_metric'           => ['=', '<', '<=', '>', '>=', 'EMPTY']
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
