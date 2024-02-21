<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query\Filter;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;

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
     * @param SearchQueryBuilder $queryBuilder
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
