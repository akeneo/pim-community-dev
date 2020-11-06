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
    public function setQueryBuilder(\Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder $queryBuilder);

    /**
     * This filter supports the operator
     *
     * @param string $operator
     */
    public function supportsOperator(string $operator): bool;

    /**
     * Filter operators
     */
    public function getOperators(): array;
}
