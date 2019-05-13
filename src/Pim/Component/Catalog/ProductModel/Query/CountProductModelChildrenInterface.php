<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\ProductModel\Query;

use Pim\Component\Catalog\Model\ProductModelInterface;

/**
 * Count the total numbers of children product models for a given list of product models.
 * The product models given as root product model are included in the total count. 
 * 
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @todo pull-up 3.x Move to `Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query`
 */
interface CountProductModelChildrenInterface
{
    /**
     * @param ProductModelInterface[] $productModels
     */
    public function forProductModels(array $productModels): int;
}
