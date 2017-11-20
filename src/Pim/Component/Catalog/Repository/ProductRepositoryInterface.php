<?php

namespace Pim\Component\Catalog\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
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
     * Get all associated products ids
     *
     * @param ProductInterface $product
     *
     * @return string[]
     */
    public function getAssociatedProductIds(ProductInterface $product);

    /**
     * Get products after the one provided. Mainly used to iterate through
     * a large collecion.
     *
     * The limit parameter defines the number of products to return.
     */
    public function searchAfter(?ProductInterface $product, int $limit): array;
}
