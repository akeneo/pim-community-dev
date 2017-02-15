<?php

namespace Pim\Component\Catalog\Query;

use Akeneo\Component\StorageUtils\Cursor\CursorFactoryInterface;
use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;
use Pim\Component\Catalog\Query\Filter\FilterRegistryInterface;
use Pim\Component\Catalog\Query\Sorter\FieldSorterInterface;
use Pim\Component\Catalog\Query\Sorter\SorterRegistryInterface;

class QueryBuilder implements ProductQueryBuilderInterface
{
    /** @var mixed */
    protected $qb;

    /** FilterRegistryInterface */
    protected $filterRegistry;

    /** SorterRegistryInterface */
    protected $sorterRegistry;

    /** @var array */
    protected $defaultContext;

    /** CursorFactoryInterface */
    protected $cursorFactory;

    /** @var array */
    protected $rawFilters = [];

    /**
     * @param FilterRegistryInterface      $filterRegistry
     * @param SorterRegistryInterface      $sorterRegistry
     * @param CursorFactoryInterface       $cursorFactory
     * @param array                        $defaultContext
     */
    public function __construct(
        FilterRegistryInterface $filterRegistry,
        SorterRegistryInterface $sorterRegistry,
        CursorFactoryInterface $cursorFactory,
        array $defaultContext
    ) {
        $this->filterRegistry = $filterRegistry;
        $this->sorterRegistry = $sorterRegistry;
        $this->cursorFactory = $cursorFactory;

        $this->defaultContext = $defaultContext;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        return $this->cursorFactory->createCursor($this->getQueryBuilder());
    }

    /**
     * {@inheritdoc}
     */
    public function setQueryBuilder($queryBuilder)
    {
        $this->qb = $queryBuilder;

        return $this;
    }

    /**
     * {@inheritdoc}
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
    public function addFilter($field, $operator, $value, array $context = [])
    {
        $filter = $this->filterRegistry->getFieldFilter($field, $operator);

        if (null === $filter) {
            throw new \LogicException(
                sprintf('Filter on property "%s" is not supported or does not support operator "%s"', $field, $operator)
            );
        }

        $this->addFieldFilter($filter, $field, $operator, $value, $context);

        $this->rawFilters[] = [
            'field'    => $field,
            'operator' => $operator,
            'value'    => $value,
            'context'  => $context
        ];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addSorter($field, $direction, array $context = [])
    {
        $sorter = $this->sorterRegistry->getFieldSorter($field);

        if (null === $sorter) {
            throw new \LogicException(
                sprintf('Sorter on field "%s" is not supported', $field)
            );
        }

        $this->addFieldSorter($sorter, $field, $direction, $context);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawFilters()
    {
        return $this->rawFilters;
    }

    /**
     * Add a filter condition on a field
     *
     * @param FieldFilterInterface $filter   the filter
     * @param string               $field    the field
     * @param string               $operator the operator
     * @param mixed                $value    the value to filter
     * @param array                $context  the filter context
     *
     * @return ProductQueryBuilderInterface
     */
    protected function addFieldFilter(FieldFilterInterface $filter, $field, $operator, $value, array $context)
    {
        $filter->setQueryBuilder($this->getQueryBuilder());
        $filter->addFieldFilter($field, $operator, $value, $context['locale'], $context['scope'], $context);

        return $this;
    }

    /**
     * Sort by field
     *
     * @param FieldSorterInterface $sorter    the sorter
     * @param string               $field     the field to sort on
     * @param string               $direction the direction to use
     * @param array                $context   the sorter context
     *
     * @return ProductQueryBuilderInterface
     */
    protected function addFieldSorter(FieldSorterInterface $sorter, $field, $direction, array $context)
    {
        $sorter->setQueryBuilder($this->getQueryBuilder());
        $sorter->addFieldSorter($field, $direction);

        return $this;
    }
}
