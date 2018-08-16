<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Query\Sorter;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\SearchQueryBuilder;

/**
 * Sorter interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SorterInterface
{
    /**
     * Inject the query builder
     *
     * @param SearchQueryBuilder $queryBuilder
     */
    public function setQueryBuilder($queryBuilder);
}
