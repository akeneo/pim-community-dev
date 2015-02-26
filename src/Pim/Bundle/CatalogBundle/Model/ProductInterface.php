<?php
namespace Pim\Bundle\CatalogBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Exception\MissingIdentifierException;
use Pim\Bundle\CommentBundle\Model\CommentSubjectInterface;
use Pim\Bundle\VersioningBundle\Model\VersionableInterface;

/**
 * Product interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductInterface extends LocalizableInterface, ScopableInterface, TimestampableInterface,
 VersionableInterface, CommentSubjectInterface, ReferableInterface
{
    /**
     * Get the ID of the product
     *
     * @return int|string
     */
    public function getId();

    /**
     * Set id
     *
     * @param int|string $id
     *
     * @return ProductInterface
     */
    public function setId($id);

    /**
     * Get the identifier of the product
     *
     * @return ProductValueInterface the identifier of the product
     *
     * @throws MissingIdentifierException if no identifier could be found
     */
    public function getIdentifier();

    /**
     * Get the product categories
     *
     * @return ArrayCollection
     */
    public function getCategories();

    /**
     * Remove a category
     *
     * @param CategoryInterface $category
     *
     * @return ProductInterface
     */
    public function removeCategory(CategoryInterface $category);

    /**
     * Add a category
     *
     * @param CategoryInterface $category
     *
     * @return ProductInterface
     */
    public function addCategory(CategoryInterface $category);

    /**
     * Get a string with categories linked to product
     *
     * @return string
     */
    public function getCategoryCodes();

    /**
     * Get values
     *
     * @return ArrayCollection
     */
    public function getValues();

    /**
     * Get value related to attribute code
     *
     * @param string $attributeCode
     * @param string $localeCode
     * @param string $scopeCode
     *
     * @return ProductValueInterface
     */
    public function getValue($attributeCode, $localeCode = null, $scopeCode = null);

    /**
     * Add value, override to deal with relation owner side
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
     *
     * @return ProductInterface
     */
    public function removeValue(ProductValueInterface $value);

    /**
     * Get the product groups
     *
     * @return ArrayCollection
     */
    public function getGroups();

    /**
     * Add a group
     *
     * @param GroupInterface $group
     *
     * @return ProductInterface
     */
    public function addGroup(GroupInterface $group);

    /**
     * Remove a group
     *
     * @param GroupInterface $group
     *
     * @return ProductInterface
     */
    public function removeGroup(GroupInterface $group);

    /**
     * Get ordered group
     *
     * @return array
     */
    public function getOrderedGroups();

    /**
     * Get the variant group of the product
     *
     * @return GroupInterface|null
     */
    public function getVariantGroup();

    /**
     * Get a string with groups
     *
     * @return string
     */
    public function getGroupCodes();

    /**
     * Get the product associations
     *
     * @return AssociationInterface[]|null
     */
    public function getAssociations();

    /**
     * Set product associations
     *
     * @param AssociationInterface[] $associations
     *
     * @return ProductInterface
     */
    public function setAssociations(array $associations = array());

    /**
     * Add product association
     *
     * @param AssociationInterface $association
     *
     * @return ProductInterface
     *
     * @throws \LogicException
     */
    public function addAssociation(AssociationInterface $association);

    /**
     * Remove product association
     *
     * @param AssociationInterface $association
     *
     * @return ProductInterface
     */
    public function removeAssociation(AssociationInterface $association);

    /**
     * Get the product association for an Association type
     *
     * @param AssociationTypeInterface $type
     *
     * @return AssociationInterface|null
     */
    public function getAssociationForType(AssociationTypeInterface $type);

    /**
     * Get the product association for an association type code
     *
     * @param string $typeCode
     *
     * @return AssociationInterface|null
     */
    public function getAssociationForTypeCode($typeCode);

    /**
     * Setter for predicate enabled
     *
     * @param bool $enabled
     *
     * @return ProductInterface
     */
    public function setEnabled($enabled);

    /**
     * Predicate to know if product is enabled or not
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * Get product completenesses
     *
     * @return ArrayCollection
     */
    public function getCompletenesses();

    /**
     * Set product completenesses
     *
     * @param ArrayCollection $completenesses
     *
     * @return ProductInterface
     */
    public function setCompletenesses(ArrayCollection $completenesses);

    /**
     * Get the attributes of the product
     *
     * @return array the attributes of the current product
     */
    public function getAttributes();

    /**
     * Get whether or not an attribute is part of a product
     *
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function hasAttribute(AttributeInterface $attribute);

    /**
     * Get the list of used attribute code from the indexed values
     *
     * @return array
     */
    public function getUsedAttributeCodes();

    /**
     * Check if an attribute can be removed from the product
     *
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function isAttributeRemovable(AttributeInterface $attribute);

    /**
     * Mark the indexed as outdated
     *
     * @return ProductInterface
     */
    public function markIndexedValuesOutdated();

    /**
     * Get all the media of the product
     *
     * @deprecated will be removed in 1.4
     *
     * @return ProductMediaInterface[]
     */
    public function getMedia();

    /**
     * Get product label
     *
     * @param string $locale
     *
     * @return mixed|string
     */
    public function getLabel($locale = null);

    /**
     * @param mixed $normalizedData
     */
    public function setNormalizedData($normalizedData);

    /**
     * Set family
     *
     * @param FamilyInterface $family
     *
     * @return ProductInterface
     */
    public function setFamily(FamilyInterface $family = null);

    /**
     * Get family
     *
     * @return FamilyInterface
     */
    public function getFamily();

    /**
     * Get family id
     *
     * @return int
     */
    public function getFamilyId();

    /**
     * Set family id
     *
     * @param int $familyId
     *
     * @return ProductInterface
     */
    public function setFamilyId($familyId);
}
