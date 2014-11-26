<?php
namespace Pim\Bundle\CatalogBundle\Model;

use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\Group;

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
     * @return boolean
     */
    public function hasProduct(ProductInterface $product);

    /**
     * Set groups
     *
     * @param Group[] $groups
     *
     * @return AssociationInterface
     */
    public function setGroups($groups);

    /**
     * Get association type
     *
     * @return AssociationType
     */
    public function getAssociationType();

    /**
     * Set association type
     *
     * @param AssociationType $associationType
     *
     * @return AssociationInterface
     */
    public function setAssociationType(AssociationType $associationType);

    /**
     * Add a group
     *
     * @param Group $group
     *
     * @return AssociationInterface
     */
    public function addGroup(Group $group);

    /**
     * Get groups
     *
     * @return Group[]
     */
    public function getGroups();

    /**
     * Remove a group
     *
     * @param Group $group
     *
     * @return AssociationInterface
     */
    public function removeGroup(Group $group);

    /**
     * Get owner
     * @return ProductInterface
     */
    public function getOwner();

    /**
     * Set owner
     *
     * @param ProductInterface $owner
     *
     * @return AssociationInterface
     */
    public function setOwner(ProductInterface $owner);
}
