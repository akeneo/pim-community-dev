<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Pim\Bundle\CatalogBundle\Model\ProductAttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\ProductAssociation;
use Pim\Bundle\CatalogBundle\Entity\Association;
use Pim\Bundle\CatalogBundle\Exception\MissingIdentifierException;

/**
 * Product interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductInterface
{
    /**
     * Get family
     *
     * @return Family
     */
    public function getFamily();

    /**
     * Set family
     *
     * @param Family $family
     *
     * @return Product
     */
    public function setFamily($family);

    /**
     * Get the identifier of the product
     *
     * @return ProductValueInterface the identifier of the product
     *
     * @throws MissingIdentifierException if no identifier could be found
     */
    public function getIdentifier();

    /**
     * Get the attributes of the product
     *
     * @return array the attributes of the current product
     */
    public function getAttributes();

    /**
     * Get values
     *
     * @return ArrayCollection
     */
    public function getValues();

    /**
     * Get product label
     *
     * @param string $locale
     *
     * @return \Pim\Bundle\FlexibleEntityBundle\Model\mixed|string
     */
    public function getLabel($locale = null);

    /**
     * Get the product categories
     *
     * @return ArrayCollection
     */
    public function getCategories();

    /**
     * Add a category
     * @param Category $category
     *
     * @return Product
     */
    public function addCategory(Category $category);

    /**
     * Remove a category
     * @param Category $category
     *
     * @return Product
     */
    public function removeCategory(Category $category);

    /**
     * Predicate to know if product is enabled or not
     *
     * @return boolean
     */
    public function isEnabled();

    /**
     * Setter for predicate enabled
     *
     * @param boolean $enabled
     *
     * @return Product
     */
    public function setEnabled($enabled);

    /**
     * Get the product groups
     *
     * @return ArrayCollection
     */
    public function getGroups();

    /**
     * Add a group
     * @param Group $group
     *
     * @return Group
     */
    public function addGroup(Group $group);

    /**
     * Remove a group
     * @param Group $group
     *
     * @return Product
     */
    public function removeGroup(Group $group);

    /**
     * Add product productAssociation
     *
     * @param ProductAssociation $productAssociation
     *
     * @return Product
     */
    public function addProductAssociation(ProductAssociation $productAssociation);

    /**
     * Remove product productAssociation
     *
     * @param ProductAssociation $productAssociation
     *
     * @return Product
     */
    public function removeProductAssociation(ProductAssociation $productAssociation);

    /**
     * Get the product productAssociations
     *
     * @return ProductAssociation[]|null
     */
    public function getProductAssociations();

    /**
     * Get the product productAssociation for an Association entity
     *
     * @param Association $association
     *
     * @return ProductAssociation|null
     */
    public function getProductAssociationForAssociation(Association $association);

    /**
     * Set product productAssociations
     *
     * @param ProductAssociation[] $productAssociations
     *
     * @return Product
     */
    public function setProductAssociations(array $productAssociations = array());

    /**
     * {@inheritdoc}
     */
    public function getReference();
}
