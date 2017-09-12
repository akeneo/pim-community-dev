<?php

declare(strict_types=1);

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

    /**
     * Return the number of existing root product models
     *
     * @return int
     */
    public function countRootProductModels(): int;

    /**
     * @param int $offset
     * @param int $size
     *
     * @return array
     */
    public function findRootProductModelsWithOffsetAndSize($offset = 0, $size = 100): array;
}
