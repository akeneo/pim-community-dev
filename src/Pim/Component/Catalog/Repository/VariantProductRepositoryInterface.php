<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Repository;

use Pim\Component\Catalog\Model\VariantProductInterface;

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
     * @param VariantProductInterface $product
     *
     * @return VariantProductInterface[]
     */
    public function findSiblingsProducts(VariantProductInterface $product): array;

    /**
     * Return the number of existing variant products
     *
     * @return int
     */
    public function countAll(): int;
}
