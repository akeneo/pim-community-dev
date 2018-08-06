<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;

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

    /**
     * Find variant products which are the direct children of the given $productModel
     *
     * @param ProductModelInterface $productModel
     *
     * @return array
     */
    public function findChildrenProducts(ProductModelInterface $productModel): array;

    /**
     * Get root products models after the one provided. Mainly used to iterate
     * through a large collecion.
     *
     * The limit parameter defines the number of products to return.
     */
    public function searchRootProductModelsAfter(?ProductModelInterface $product, int $limit): array;

    /**
     * @param FamilyVariantInterface $familyVariant
     *
     * @return array
     */
    public function findSubProductModels(FamilyVariantInterface $familyVariant): array;

    /**
     * @param FamilyVariantInterface $familyVariant
     *
     * @return array
     */
    public function findRootProductModels(FamilyVariantInterface $familyVariant): array;

    /**
     * @param FamilyVariantInterface $familyVariant
     *
     * @return array
     */
    public function findProductModelsForFamilyVariant(FamilyVariantInterface $familyVariant, ?string $search = null): array;

    /**
     * @param FamilyVariantInterface $familyVariant
     * @param String                 $search
     * @param int                    $limit
     * @param int                    $page
     *
     * @return array
     */
    public function searchLastLevelByCode(
        FamilyVariantInterface $familyVariant,
        string $search,
        int $limit,
        int $page = 0
    ): array;
}
