<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;

/**
 * Aims to customize a query builder to add useful shortcuts which allow to easily select, filter or sort a product
 * values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductQueryBuilderInterface
{
    /**
     * Add a filter condition on a field
     *
     * @param string $field    the field
     * @param string $operator the used operator
     * @param mixed  $value    the value to filter
     * @param array  $context  the filter context, used for locale and scope
     *
     * @throws \LogicException
     */
    public function addFilter(string $field, string $operator, $value, array $context = []): \Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;

    /**
     * Sort by field
     *
     * @param string $field     the field to sort on
     * @param string $direction the direction to use
     * @param array  $context   the sorter context, used for locale and scope
     *
     * @throws \LogicException
     */
    public function addSorter(string $field, string $direction, array $context = []): \Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;

    /**
     * Returns applied filters
     */
    public function getRawFilters(): array;

    /**
     * Get query builder
     *
     * @throws \LogicException in case the query builder has not been configured
     */
    public function getQueryBuilder(): SearchQueryBuilder;

    /**
     * Set query builder
     *
     * @param mixed $queryBuilder
     */
    public function setQueryBuilder($queryBuilder): \Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;

    /**
     * Executes a query
     */
    public function execute(): CursorInterface;
}
