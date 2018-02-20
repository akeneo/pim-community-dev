<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;

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
