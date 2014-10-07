<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Query;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;

/**
 * Builds a product query builder by using  shortcuts to easily select, filter or sort products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductQueryBuilder implements ProductQueryBuilderInterface
{
    /** @var AttributeRepository */
    protected $attributeRepository;

    /** @var mixed */
    protected $qb;

    /** QueryFilterRegistryInterface */
    protected $filterRegistry;

    /** QuerySorterRegistryInterface */
    protected $sorterRegistry;

    /**
     * Constructor
     *
     * @param AttributeRepository          $attributeRepository
     * @param QueryFilterRegistryInterface $filterRegistry
     * @param QuerySorterRegistryInterface $sorterRegistry
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        QueryFilterRegistryInterface $filterRegistry,
        QuerySorterRegistryInterface $sorterRegistry
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->filterRegistry = $filterRegistry;
        $this->sorterRegistry = $sorterRegistry;
    }

    /**
     * Get query builder
     *
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\ODM\MongoDB\Query\Builder $qb
     *
     * @return ProductQueryBuilderInterface
     */
    public function setQueryBuilder($qb)
    {
        $this->qb = $qb;

        return $this;
    }

    /**
     * Get query builder
     *
     * @return \Doctrine\ORM\QueryBuilder|\Doctrine\ODM\MongoDB\Query\Builder
     * @throws \LogicException
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
    public function addFilter($field, $operator, $value)
    {
        $attribute = $this->attributeRepository->findOneByCode($field);

        if ($attribute !== null) {
            $filter = $this->filterRegistry->getAttributeFilter($attribute);
        } else {
            $filter = $this->filterRegistry->getFieldFilter($field);
        }

        if ($filter === null) {
            throw new \LogicException(
                sprintf('Filter on field "%s" is not supported', $field)
            );
        }

        if ($filter->supportsOperator($operator) === false) {
            throw new \LogicException(
                sprintf('Filter on field "%s" doesn\'t provide operator "%s"', $field, $operator)
            );
        }

        if ($attribute !== null) {
            $this->addAttributeFilter($filter, $attribute, $operator, $value);
        } else {
            $this->addFieldFilter($filter, $field, $operator, $value);
        }

        return $this;
    }

    /**
     * Sort by field
     *
     * @param string $field     the field to sort on
     * @param string $direction the direction to use
     *
     * @throws \LogicException
     *
     * @return ProductQueryBuilderInterface
     */
    public function addSorter($field, $direction)
    {
        $attribute = $this->attributeRepository->findOneByCode($field);

        if ($attribute !== null) {
            $sorter = $this->sorterRegistry->getAttributeSorter($attribute);
        } else {
            $sorter = $this->sorterRegistry->getFieldSorter($field);
        }

        if ($sorter === null) {
            throw new \LogicException(
                sprintf('Sorter on field "%s" is not supported', $field)
            );
        }

        if ($attribute !== null) {
            $this->addAttributeSorter($sorter, $attribute, $direction);
        } else {
            $this->addFieldSorter($sorter, $field, $direction);
        }

        return $this;
    }

    /**
     * Add a filter condition on a field
     *
     * @param FieldFilterInterface $filter   the filter
     * @param string               $field    the field
     * @param string               $operator the operator
     * @param mixed                $value    the value to filter
     *
     * @return ProductQueryBuilderInterface
     */
    protected function addFieldFilter(FieldFilterInterface $filter, $field, $operator, $value)
    {
        $filter->setQueryBuilder($this->getQueryBuilder());
        $filter->addFieldFilter($field, $operator, $value);

        return $this;
    }

    /**
     * Add a filter condition on an attribute
     *
     * @param AttributeFilterInterface $filter    the filter
     * @param AttributeInterface       $attribute the attribute
     * @param string                   $operator  the operator
     * @param mixed                    $value     the value to filter
     *
     * @return ProductQueryBuilderInterface
     */
    protected function addAttributeFilter(
        AttributeFilterInterface $filter,
        AttributeInterface $attribute,
        $operator,
        $value
    ) {
        $filter->setQueryBuilder($this->getQueryBuilder());
        $filter->addAttributeFilter($attribute, $operator, $value);

        return $this;
    }

    /**
     * Sort by field
     *
     * @param FieldSorterInterface $sorter    the sorter
     * @param string               $field     the field to sort on
     * @param string               $direction the direction to use
     *
     * @return ProductQueryBuilderInterface
     */
    protected function addFieldSorter(FieldSorterInterface $sorter, $field, $direction)
    {
        $sorter->setQueryBuilder($this->getQueryBuilder());
        $sorter->addFieldSorter($field, $direction);

        return $this;
    }

    /**
     * Sort by attribute value
     *
     * @param AttributeSorterInterface $sorter    the sorter
     * @param AttributeInterface       $attribute the attribute to sort on
     * @param string                   $direction the direction to use
     *
     * @return ProductQueryBuilderInterface
     */
    protected function addAttributeSorter(AttributeSorterInterface $sorter, AttributeInterface $attribute, $direction)
    {
        $sorter->setQueryBuilder($this->getQueryBuilder());
        $sorter->addAttributeSorter($attribute, $direction);

        return $this;
    }
}
