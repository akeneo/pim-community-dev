<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface VariantProductRepositoryInterface
{
    /**
     * Finds products with the same parent than the provided $product.
     *
     * @param ProductInterface $product
     *
     * @return ProductInterface[]
     */
    public function findSiblingsProducts(ProductInterface $product): array;

    /**
     * @param ProductModelInterface $parent
     *
     * @return null|ProductInterface
     */
    public function findLastCreatedByParent(ProductModelInterface $parent): ?ProductInterface;
}
