<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;
use Akeneo\Tool\Component\StorageUtils\Model\ReferableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Association interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AssociationInterface extends ReferableInterface
{
    /**
     * Get id
     *
     * @return int|string
     */
    public function getId();

    /**
     * Get products
     *
     * @return ProductInterface[]|ArrayCollection
     */
    public function getProducts();

    /**
     * Set products
     *
     * @param ProductInterface[] $products
     *
     * @return AssociationInterface
     */
    public function setProducts($products);

    /**
     * Add a product
     *
     * @param ProductInterface $product
     *
     * @return AssociationInterface
     */
    public function addProduct(ProductInterface $product);

    /**
     * Remove a product
     *
     * @param ProductInterface $product
     *
     * @return AssociationInterface
     */
    public function removeProduct(ProductInterface $product);

    /**
     * Has a product
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    public function hasProduct(ProductInterface $product);

    /**
     * Get product models
     */
    public function getProductModels(): Collection;

    /**
     * Add a product model
     *
     * @param ProductModelInterface $productModel
     */
    public function addProductModel(ProductModelInterface $productModel): void;

    /**
     * Remove a product model
     *
     * @param ProductModelInterface $productModel
     */
    public function removeProductModel(ProductModelInterface $productModel): void;

    /**
     * Set a product model collection.
     *
     * @param ProductModelInterface[] $productModels
     */
    public function setProductModels($productModels): void;

    /**
     * Set groups
     *
     * @param GroupInterface[] $groups
     *
     * @return AssociationInterface
     */
    public function setGroups($groups);

    /**
     * Get association type
     *
     * @return AssociationTypeInterface
     */
    public function getAssociationType();

    /**
     * Set association type
     *
     * @param AssociationTypeInterface $associationType
     *
     * @return AssociationInterface
     */
    public function setAssociationType(AssociationTypeInterface $associationType);

    /**
     * Add a group
     *
     * @param GroupInterface $group
     *
     * @return AssociationInterface
     */
    public function addGroup(GroupInterface $group);

    /**
     * Get groups
     *
     * @return GroupInterface[]|ArrayCollection
     */
    public function getGroups();

    /**
     * Remove a group
     *
     * @param GroupInterface $group
     *
     * @return AssociationInterface
     */
    public function removeGroup(GroupInterface $group);

    /**
     * Get owner
     *
     * @return EntityWithAssociationsInterface
     */
    public function getOwner();

    /**
     * Set owner
     *
     * @param EntityWithAssociationsInterface $owner
     *
     * @return AssociationInterface
     */
    public function setOwner(EntityWithAssociationsInterface $owner);
}
