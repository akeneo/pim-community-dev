<?php

namespace Pim\Bundle\CatalogBundle\Repository;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\NonUniqueResultException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;

/**
 * Product repository interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductRepositoryInterface
{
    /**
     * Load a product entity with related attribute values
     *
     * @param int $id
     *
     * @throws NonUniqueResultException
     *
     * @return ProductInterface|null
     */
    public function findOneByWithValues($id);

    /**
     * @param ChannelInterface $channel
     *
     * @return mixed
     */
    public function buildByChannelAndCompleteness(ChannelInterface $channel);

    /**
     * @param array $ids
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findByIds(array $ids);

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
     * Returns all products that have the given attribute
     *
     * @param AttributeInterface $attribute
     *
     * @return ProductInterface[]
     */
    public function findAllWithAttribute(AttributeInterface $attribute);

    /**
     * Returns all products that have the given attribute option
     *
     * @param AttributeOptionInterface $option
     *
     * @return ProductInterface[]
     */
    public function findAllWithAttributeOption(AttributeOptionInterface $option);

    /**
     * Returns a full product with all relations
     *
     * @param int $id
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductInterface
     */
    public function getFullProduct($id);

    /**
     * Returns true if a ProductValue with the provided value already exists,
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
     * Get full products from product ids
     *
     * @param array $productIds
     * @param array $attributeIds
     *
     * @return array
     */
    public function getFullProducts(array $productIds, array $attributeIds = []);

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
     *
     * @return ProductInterface|null
     */
    public function findAll();

    /**
     * @param string|int $id
     *
     * @return array ProductInterface|null
     */
    public function findOneById($id);

    /**
     * @param int $variantGroupId
     *
     * @return array product ids
     */
    public function getEligibleProductIdsForVariantGroup($variantGroupId);

    /**
     * @param GroupInterface $group
     * @param                $maxResults
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
}
