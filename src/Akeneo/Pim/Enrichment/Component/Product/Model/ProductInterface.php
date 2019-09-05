<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Model;

use Akeneo\Pim\Enrichment\Component\Comment\Model\CommentSubjectInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Model\ReferableInterface;
use Akeneo\Tool\Component\Versioning\Model\TimestampableInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Product interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductInterface extends
    TimestampableInterface,
    VersionableInterface,
    CommentSubjectInterface,
    ReferableInterface,
    CategoryAwareInterface,
    EntityWithFamilyInterface,
    EntityWithFamilyVariantInterface,
    EntityWithAssociationsInterface
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
     * @return string
     */
    public function getIdentifier();

    /**
     * @param string|null $identifierValue
     *
     * @return ProductInterface
     */
    public function setIdentifier(?string $identifierValue): ProductInterface;

    /**
     * Get the product groups
     *
     * @return Collection
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
     * @param Collection $groups
     */
    public function setGroups(Collection $groups): void;

    /**
     * Remove a group
     *
     * @param GroupInterface $group
     *
     * @return ProductInterface
     */
    public function removeGroup(GroupInterface $group);

    /**
     * Get groups code
     *
     * @return array
     */
    public function getGroupCodes();

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
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function hasAttributeInFamily(AttributeInterface $attribute);

    /**
     * Check if an attribute can be removed from the product
     *
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function isAttributeRemovable(AttributeInterface $attribute);

    /**
     * Check if an attribute can be edited from the product
     *
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function isAttributeEditable(AttributeInterface $attribute);

    /**
     * Get product image
     *
     * @return mixed|null
     */
    public function getImage();

    /**
     * Get product label
     *
     * @param string $locale
     * @param string $scopeCode
     *
     * @return mixed|string
     */
    public function getLabel($locale = null, $scopeCode = null);

    /**
     * Set family
     *
     * @param FamilyInterface $family
     *
     * @return ProductInterface
     */
    public function setFamily(FamilyInterface $family = null);

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

    /**
     * @return ArrayCollection
     */
    public function getUniqueData();

    /**
     * @param ProductUniqueDataInterface $uniqueData
     *
     * @return ProductInterface
     */
    public function addUniqueData(ProductUniqueDataInterface $uniqueData);

    /**
     * @param Collection $data
     */
    public function setUniqueData(Collection $data): void;

    /**
     * @return bool
     */
    public function isVariant(): bool;

    /**
     * Return the categories for a variation
     *
     * @return Collection
     */
    public function getCategoriesForVariation(): Collection;
}
