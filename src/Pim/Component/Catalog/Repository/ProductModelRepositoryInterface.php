<?php

namespace Pim\Component\Catalog\Repository;

use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Component\Catalog\Model\ProductModelInterface;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface ProductModelRepositoryInterface extends
    ObjectRepository,
    IdentifiableObjectRepositoryInterface,
    CursorableRepositoryInterface
{
    /**
     * Find product models with the same parent than the given $productModel
     *
     * @param ProductModelInterface $productModel
     *
     * @return ProductModelInterface[]
     */
    public function findSiblingsProductModels(ProductModelInterface $productModel): array;
}
