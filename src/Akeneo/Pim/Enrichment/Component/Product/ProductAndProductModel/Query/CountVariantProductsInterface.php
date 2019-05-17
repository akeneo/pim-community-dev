<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\ProductAndProductModel\Query;

/**
 * Count the total number of variant products for a given list of product models.
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CountVariantProductsInterface
{
    /**
     * @param string[] $productModelCodes
     */
    public function forProductModelCodes(array $productModelCodes): int;
}
