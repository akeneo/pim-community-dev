<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Grid\Query;

use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Rows;

/**
 * Ideally, we should inject a Product Query with all the filters and not a product query builder.
 * Then, this query could be executed with the service of our choice (ES, Mysql, fake).
 *
 * But the current implementation of the query builder directly
 * contains the filters and has the responsibility of executing the query.
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FetchProductAndProductModelRows
{
    /**
     * @param FetchProductAndProductModelRowsParameters $queryParameters
     *
     * @return Rows
     */
    public function __invoke(FetchProductAndProductModelRowsParameters $queryParameters): Rows;
}
