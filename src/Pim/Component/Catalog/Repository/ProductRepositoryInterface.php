<?php

namespace Pim\Component\Catalog\Repository;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\NonUniqueResultException;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;

/**
 * Product repository interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductRepositoryInterface extends ObjectRepository
{
    /**
     * Find all products in a variant group (by variant axis attribute values)
     *
     * @param GroupInterface $variantGroup the variant group
     * @param array          $criteria     the criteria
     *
     * @return array
     */
    public function findAllForVariantGroup(GroupInterface $variantGroup, array $criteria = []);

    /**
     * @param ProductQueryBuilderFactoryInterface $factory
     *
     * @return ProductRepositoryInterface
     */
    public function setProductQueryBuilderFactory(ProductQueryBuilderFactoryInterface $factory);

    /**
     * Get available attribute ids from a product ids list
     *
     * @param array $productIds
     *
     * @return array
     */
    public function getAvailableAttributeIdsToExport(array $productIds);

    /**
     * @param string $identifier
     *
     * @return ProductInterface|null
     */
    public function findOneByIdentifier($identifier);

    /**
     * @param int $variantGroupId
     *
     * @return CursorInterface
     */
    public function getEligibleProductsForVariantGroup($variantGroupId);

    /**
     * @param GroupInterface $group
     * @param int            $maxResults
     *
     * @return array
     */
    public function getProductsByGroup(GroupInterface $group, $maxResults);

    /**
     * @param GroupInterface $group
     *
     * @return int
     */
    public function getProductCountByGroup(GroupInterface $group);

    /**
     * Return the number of existing products
     *
     * @return int
     */
    public function countAll();

    /**
     * Checks if the family has the specified attribute
     *
     * @param mixed  $productId
     * @param string $attributeCode
     *
     * @return bool
     */
    public function hasAttributeInFamily($productId, $attributeCode);

    /**
     * @param GroupInterface $variantGroup
     * @param array          $criteria
     *
     * @return array
     */
    public function findProductIdsForVariantGroup(GroupInterface $variantGroup, array $criteria = []);

    /**
     * @param int $offset
     * @param int $size
     *
     * @return array
     */
    public function findAllWithOffsetAndSize($offset = 0, $size = 100);

    /**
     * Get all associated products ids
     *
     * @param ProductInterface $product
     *
     * @return string[]
     */
    public function getAssociatedProductIds(ProductInterface $product);
}
