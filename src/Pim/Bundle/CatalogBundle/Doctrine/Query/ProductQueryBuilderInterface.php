<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Query;

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
     * @param string $value    the value to filter
     *
     * @throws \LogicException
     *
     * @return ProductQueryBuilderInterface
     */
    public function addFilter($field, $operator, $value);

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
    public function addSorter($field, $direction);

    /**
     * Get the field filter
     * TODO : introduce registry filter and sorter and use them in PQB
     *
     * @param string $field the field
     *
     * @throws \LogicException
     *
     * @return FilterInterface
     */
    public function getFilter($field);
}
