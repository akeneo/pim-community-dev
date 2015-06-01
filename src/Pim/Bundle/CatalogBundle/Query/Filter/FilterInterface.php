<?php

namespace Pim\Bundle\CatalogBundle\Query\Filter;

/**
 * Filter interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FilterInterface
{
    /**
     * Inject the query builder
     *
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\ODM\MongoDB\Query\Builder $queryBuilder
     */
    public function setQueryBuilder($queryBuilder);

    /**
     * This filter supports the operator
     *
     * @param string $operator
     *
     * @return bool
     */
    public function supportsOperator($operator);

    /**
     * Filter operators
     *
     * @return array
     */
    public function getOperators();
}
