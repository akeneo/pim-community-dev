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
    EntityWithAssociationsInterface,
    EntityWithQuantifiedAssociationsInterface
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
     */
    public function setId($id): \Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

    /**
     * @param string|null $identifierValue
     *
     * @return ProductInterface
     */
    public function setIdentifier(?string $identifierValue): ProductInterface;

    /**
     * Get the product groups
     */
    public function getGroups(): \Doctrine\Common\Collections\Collection;

    /**
     * Add a group
     *
     * @param GroupInterface $group
     */
    public function addGroup(GroupInterface $group): \Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

    /**
     * @param Collection $groups
     */
    public function setGroups(Collection $groups): void;

    /**
     * Remove a group
     *
     * @param GroupInterface $group
     */
    public function removeGroup(GroupInterface $group): \Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

    /**
     * Get groups code
     */
    public function getGroupCodes(): array;

    /**
     * Setter for predicate enabled
     *
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): \Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

    /**
     * Predicate to know if product is enabled or not
     */
    public function isEnabled(): bool;

    /**
     * @param AttributeInterface $attribute
     */
    public function hasAttributeInFamily(AttributeInterface $attribute): bool;

    /**
     * Check if an attribute can be removed from the product
     *
     * @param AttributeInterface $attribute
     */
    public function isAttributeRemovable(AttributeInterface $attribute): bool;

    /**
     * Check if an attribute can be edited from the product
     *
     * @param AttributeInterface $attribute
     */
    public function isAttributeEditable(AttributeInterface $attribute): bool;

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
    public function getLabel(string $locale = null, string $scopeCode = null);

    /**
     * Set family
     *
     * @param FamilyInterface $family
     */
    public function setFamily(FamilyInterface $family = null): \Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

    /**
     * Get family id
     */
    public function getFamilyId(): int;

    public function getUniqueData(): ArrayCollection;

    /**
     * @param ProductUniqueDataInterface $uniqueData
     */
    public function addUniqueData(ProductUniqueDataInterface $uniqueData): \Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

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
