<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Grid\Query;

use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;

/**
 * This is an extension point to add additional properties into the product model rows of the product grid.
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AddAdditionalProductModelProperties
{
    /**
     * @param FetchProductAndProductModelRowsParameters $queryParameters
     * @param Row[]                                     $rows
     *
     * @return Row[]
     */
    public function add(FetchProductAndProductModelRowsParameters $queryParameters, array $rows): array;
}
