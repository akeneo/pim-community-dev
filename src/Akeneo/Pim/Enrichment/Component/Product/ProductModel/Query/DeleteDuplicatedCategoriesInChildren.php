<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query;

/**
 * Delete the duplicated categories of the children of given product models.
 * (i.e. any category linked to a product variant or product model that is already linked to its parent)
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DeleteDuplicatedCategoriesInChildren
{
    /**
     * @param string[] $productModelCodes
     */
    public function forProductModelCodes(array $productModelCodes): void;
}
