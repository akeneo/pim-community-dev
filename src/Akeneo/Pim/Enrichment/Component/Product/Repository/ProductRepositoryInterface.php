<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Doctrine\Common\Persistence\ObjectRepository;

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
     */
    public function getAvailableAttributeIdsToExport(array $productIds): array;

    /**
     * @param string $identifier
     */
    public function findOneByIdentifier(string $identifier): ?ProductInterface;

    /**
     * @param GroupInterface $group
     * @param int            $maxResults
     */
    public function getProductsByGroup(GroupInterface $group, int $maxResults): array;

    /**
     * @param GroupInterface $group
     */
    public function getProductCountByGroup(GroupInterface $group): int;

    /**
     * Return the number of existing products
     *
     * @return int
     */
    public function countAll(): int;

    /**
     * Checks if the family has the specified attribute
     *
     * @param mixed  $productId
     * @param string $attributeCode
     */
    public function hasAttributeInFamily($productId, string $attributeCode): bool;

    /**
     * Get products after the one provided. Mainly used to iterate through
     * a large collecion.
     *
     * The limit parameter defines the number of products to return.
     */
    public function searchAfter(?ProductInterface $product, int $limit): array;
}
