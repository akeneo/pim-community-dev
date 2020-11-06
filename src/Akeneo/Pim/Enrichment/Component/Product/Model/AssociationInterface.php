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
     */
    public function setProducts(array $products): \Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;

    /**
     * Add a product
     *
     * @param ProductInterface $product
     */
    public function addProduct(ProductInterface $product): \Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;

    /**
     * Remove a product
     *
     * @param ProductInterface $product
     */
    public function removeProduct(ProductInterface $product): \Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;

    /**
     * Has a product
     *
     * @param ProductInterface $product
     */
    public function hasProduct(ProductInterface $product): bool;

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
    public function setProductModels(array $productModels): void;

    /**
     * Set groups
     *
     * @param GroupInterface[] $groups
     */
    public function setGroups(array $groups): \Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;

    /**
     * Get association type
     */
    public function getAssociationType(): \Akeneo\Pim\Structure\Component\Model\AssociationTypeInterface;

    /**
     * Set association type
     *
     * @param AssociationTypeInterface $associationType
     */
    public function setAssociationType(AssociationTypeInterface $associationType): \Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;

    /**
     * Add a group
     *
     * @param GroupInterface $group
     */
    public function addGroup(GroupInterface $group): \Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;

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
     */
    public function removeGroup(GroupInterface $group): \Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;

    /**
     * Get owner
     */
    public function getOwner(): \Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;

    /**
     * Set owner
     *
     * @param EntityWithAssociationsInterface $owner
     */
    public function setOwner(EntityWithAssociationsInterface $owner): \Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
}
