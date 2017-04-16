<?php

namespace Pim\Component\Catalog\Repository;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\NonUniqueResultException;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
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
     * @param ChannelInterface $channel
     *
     * @return mixed
     */
    public function buildByChannelAndCompleteness(ChannelInterface $channel);

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
     * Returns true if a ProductValue with the provided value alread exists,
     * false otherwise.
     *
     * @param ProductValueInterface $value
     *
     * @return bool
     */
    public function valueExists(ProductValueInterface $value);

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
     * @return ObjectManager
     */
    public function getObjectManager();

    /**
     * @param string $identifier
     *
     * @return ProductInterface|null
     */
    public function findOneByIdentifier($identifier);

    /**
     * @param string|int $id
     *
     * @return ProductInterface|null
     *
     * @deprecated
     */
    public function findOneById($id);

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
     * Checks if the group has the specified attribute
     *
     * @param mixed  $productId
     * @param string $attributeCode
     *
     * @return bool
     */
    public function hasAttributeInVariantGroup($productId, $attributeCode);

    /**
     * @param GroupInterface $variantGroup
     * @param array          $criteria
     *
     * @return array
     */
    public function findProductIdsForVariantGroup(GroupInterface $variantGroup, array $criteria = []);
}
