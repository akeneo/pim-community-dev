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

    /**
     * Find product models which are the direct children of the given $productModel
     *
     * @param ProductModelInterface $productModel
     *
     * @return array|ProductModelInterface[]
     */
    public function findChildrenProductModels(ProductModelInterface $productModel): array;

    /**
     * Returns the identifiers of the products belonging to a product model descendants subtree
     *
     * @param ProductModelInterface $productModel
     *
     * @return array
     */
    public function findDescendantProductIdentifiers(ProductModelInterface $productModel): array;

    /**
     * Find several product models by their identifier
     *
     * @param array $codes
     *
     * @return ProductModelInterface[]
     */
    public function findByIdentifiers(array $codes): array;
}
