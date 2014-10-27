<?php

namespace Pim\Bundle\CatalogBundle\Repository;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Doctrine\Query\ProductQueryFactoryInterface;

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
     * @param integer $id
     *
     * @return Product|null
     * @throws NonUniqueResultException
     */
    public function findOneByWithValues($id);

    /**
     * @param Channel $channel
     *
     * @return mixed
     */
    public function buildByChannelAndCompleteness(Channel $channel);

    /**
     * @param array $ids
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findByIds(array $ids);

    /**
     * Find all products in a variant group (by variant axis attribute values)
     *
     * @param Group $variantGroup the variant group
     * @param array $criteria     the criteria
     *
     * @return array
     */
    public function findAllForVariantGroup(Group $variantGroup, array $criteria = array());

    /**
     * Returns all products that have the given attribute
     *
     * @param AbstractAttribute $attribute
     *
     * @return ProductInterface[]
     */
    public function findAllWithAttribute(AbstractAttribute $attribute);

    /**
     * Returns all products that have the given attribute option
     *
     * @param AttributeOption $option
     *
     * @return ProductInterface[]
     */
    public function findAllWithAttributeOption(AttributeOption $option);

    /**
     * Returns a full product with all relations
     *
     * @param integer $id
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductInterface
     */
    public function getFullProduct($id);

    /**
     * Returns true if a ProductValue with the provided value alread exists,
     * false otherwise.
     *
     * @param ProductValueInterface $value
     *
     * @return boolean
     */
    public function valueExists(ProductValueInterface $value);

    /**
     * @param ProductQueryFactoryInterface $factory
     *
     * @return ProductRepositoryInterface
     */
    public function setProductQueryFactory(ProductQueryFactoryInterface $factory);

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
    public function getFullProducts(array $productIds, array $attributeIds = array());

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
     * @param string|integer $id
     *
     * @return ProductInterface|null
     */
    public function findOneById($id);
}
