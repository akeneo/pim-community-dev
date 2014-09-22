<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Exception\MissingIdentifierException;
use Pim\Bundle\CommentBundle\Model\CommentSubjectInterface;

/**
 * Product interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductInterface extends CommentSubjectInterface
{
    /**
     * Get the ID of the product
     *
     * @return int
     */
    public function getId();

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return ProductInterface
     */
    public function setId($id);

    /**
     * Get created datetime
     *
     * @return \DateTime
     */
    public function getCreated();

    /**
     * Get updated created datetime
     *
     * @return \DateTime
     */
    public function getUpdated();

    /**
     * Add value
     *
     * @param ProductValueInterface $value
     *
     * @return ProductInterface
     */
    public function addValue(ProductValueInterface $value);

    /**
     * Remove value
     *
     * @param ProductValueInterface $value
     */
    public function removeValue(ProductValueInterface $value);

    /**
     * Get values
     *
     * @return \ArrayAccess
     */
    public function getValues();

    /**
     * Get value related to attribute code
     *
     * @param string $attributeCode
     *
     * @return ProductValueInterface
     */
    public function getValue($attributeCode);

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
    public function setFamily(Family $family = null);

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
     * Get product label
     *
     * @param string $locale
     *
     * @return mixed|string
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
     * @param CategoryInterface $category
     *
     * @return Product
     */
    public function addCategory(CategoryInterface $category);

    /**
     * Remove a category
     * @param CategoryInterface $category
     *
     * @return Product
     */
    public function removeCategory(CategoryInterface $category);

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
     * Add product association
     *
     * @param AbstractAssociation $association
     *
     * @return Product
     */
    public function addAssociation(AbstractAssociation $association);

    /**
     * Remove product association
     *
     * @param AbstractAssociation $association
     *
     * @return Product
     */
    public function removeAssociation(AbstractAssociation $association);

    /**
     * Get the product associations
     *
     * @return AbstractAssociation[]|null
     */
    public function getAssociations();

    /**
     * Get the product association for an AssociationType entity
     *
     * @param AssociationType $association
     *
     * @return AbstractAssociation|null
     */
    public function getAssociationForType(AssociationType $association);

    /**
     * Get the product association for an association type code
     *
     * @param string $typeCode
     *
     * @return AbstractAssociation|null
     */
    public function getAssociationForTypeCode($typeCode);

    /**
     * Set product associations
     *
     * @param AbstractAssociation[] $associations
     *
     * @return Product
     */
    public function setAssociations(array $associations = array());

    /**
     * Set product completenesses
     *
     * @param ArrayCollection $completenesses
     *
     * @return Product
     */
    public function setCompletenesses(ArrayCollection $completenesses);

    /**
     * Get product completenesses
     *
     * @return ArrayCollection
     */
    public function getCompletenesses();

    /**
     * {@inheritdoc}
     */
    public function getReference();

    /**
     * Get a string with categories linked to product
     *
     * @return string
     */
    public function getCategoryCodes();

    /**
     * Get a string with groups
     *
     * @return string
     */
    public function getGroupCodes();
}
