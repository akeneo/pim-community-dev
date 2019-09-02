<?php

namespace Akeneo\Pim\Structure\Component\Model;

use Akeneo\Tool\Component\Localization\Model\TranslatableInterface;
use Akeneo\Tool\Component\StorageUtils\Model\ReferableInterface;
use Akeneo\Tool\Component\Versioning\Model\TimestampableInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use Doctrine\Common\Collections\Collection;

/**
 * Family interface
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FamilyInterface extends
    TranslatableInterface,
    ReferableInterface,
    VersionableInterface,
    TimestampableInterface
{
    /**
     * Get id
     *
     * @return int
     */
    public function getId();

    /**
     * Get code
     *
     * @return string $code
     */
    public function getCode();

    /**
     * Set code
     *
     * @param string $code
     *
     * @return FamilyInterface
     */
    public function setCode($code);

    /**
     * Add attribute
     *
     * @param AttributeInterface $attribute
     *
     * @return FamilyInterface
     */
    public function addAttribute(AttributeInterface $attribute);

    /**
     * Remove attribute
     *
     * @param AttributeInterface $attribute
     *
     * @return FamilyInterface
     */
    public function removeAttribute(AttributeInterface $attribute);

    /**
     * Get attributes
     *
     * @return AttributeInterface[]|Collection
     */
    public function getAttributes();

    /**
     * Get attribute codes
     *
     * @return string[]
     */
    public function getAttributeCodes();

    /**
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function hasAttribute(AttributeInterface $attribute);

    /**
     * @param string $attributeCode
     *
     * @return bool
     */
    public function hasAttributeCode($attributeCode);

    /**
     * @param AttributeInterface $attributeAsLabel
     *
     * @return FamilyInterface
     */
    public function setAttributeAsLabel(AttributeInterface $attributeAsLabel);

    /**
     * @return AttributeInterface|null
     */
    public function getAttributeAsLabel();

    /**
     * @param AttributeInterface|null $attributeAsImage
     *
     * @return FamilyInterface
     */
    public function setAttributeAsImage(?AttributeInterface $attributeAsImage): FamilyInterface;

    /**
     * @return AttributeInterface
     */
    public function getAttributeAsImage(): ?AttributeInterface;

    /**
     * Add attribute requirement
     *
     * @param AttributeRequirementInterface $requirement
     *
     * @return FamilyInterface
     */
    public function addAttributeRequirement(AttributeRequirementInterface $requirement);

    /**
     * Remove attribute requirement
     *
     * @param AttributeRequirementInterface $requirement
     *
     * @return FamilyInterface
     */
    public function removeAttributeRequirement(AttributeRequirementInterface $requirement);

    /**
     * Set attributes requirements
     *
     * @param AttributeRequirementInterface[] $requirements
     *
     * @return FamilyInterface
     */
    public function setAttributeRequirements(array $requirements);

    /**
     * Get attribute requirements
     *
     * @return AttributeRequirementInterface[]
     */
    public function getAttributeRequirements();

    /**
     * Get grouped attributes
     *
     * @return AttributeInterface[]
     */
    public function getGroupedAttributes();

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Set label
     *
     * @param string $label
     *
     * @return FamilyInterface
     */
    public function setLabel($label);

    /**
     * Get attribute requirement key
     *
     * @param AttributeRequirementInterface $requirement
     *
     * @return string
     */
    public function getAttributeRequirementKey(AttributeRequirementInterface $requirement);

    /**
     * @return array
     */
    public function getAttributeAsLabelChoices();

    /**
     * @return Collection
     */
    public function getFamilyVariants(): Collection;

    /**
     * @param Collection $familyVariants
     */
    public function setFamilyVariants(Collection $familyVariants): void;
}
