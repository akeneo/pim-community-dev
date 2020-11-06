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
     */
    public function getId(): int;

    /**
     * Get code
     *
     * @return string $code
     */
    public function getCode(): string;

    /**
     * Set code
     *
     * @param string $code
     */
    public function setCode(string $code): \Akeneo\Pim\Structure\Component\Model\FamilyInterface;

    /**
     * Add attribute
     *
     * @param AttributeInterface $attribute
     */
    public function addAttribute(AttributeInterface $attribute): \Akeneo\Pim\Structure\Component\Model\FamilyInterface;

    /**
     * Remove attribute
     *
     * @param AttributeInterface $attribute
     */
    public function removeAttribute(AttributeInterface $attribute): \Akeneo\Pim\Structure\Component\Model\FamilyInterface;

    /**
     * Get attributes
     *
     * @return AttributeInterface[]|Collection
     */
    public function getAttributes(): \Doctrine\Common\Collections\Collection;

    /**
     * Get attribute codes
     *
     * @return string[]
     */
    public function getAttributeCodes(): array;

    /**
     * @param AttributeInterface $attribute
     */
    public function hasAttribute(AttributeInterface $attribute): bool;

    /**
     * @param string $attributeCode
     */
    public function hasAttributeCode(string $attributeCode): bool;

    /**
     * @param AttributeInterface $attributeAsLabel
     */
    public function setAttributeAsLabel(AttributeInterface $attributeAsLabel): \Akeneo\Pim\Structure\Component\Model\FamilyInterface;

    public function getAttributeAsLabel(): ?\Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * @param AttributeInterface|null $attributeAsImage
     *
     * @return FamilyInterface
     */
    public function setAttributeAsImage(?AttributeInterface $attributeAsImage): FamilyInterface;

    public function getAttributeAsImage(): \Akeneo\Pim\Structure\Component\Model\AttributeInterface;

    /**
     * Add attribute requirement
     *
     * @param AttributeRequirementInterface $requirement
     */
    public function addAttributeRequirement(AttributeRequirementInterface $requirement): \Akeneo\Pim\Structure\Component\Model\FamilyInterface;

    /**
     * Remove attribute requirement
     *
     * @param AttributeRequirementInterface $requirement
     */
    public function removeAttributeRequirement(AttributeRequirementInterface $requirement): \Akeneo\Pim\Structure\Component\Model\FamilyInterface;

    /**
     * Set attributes requirements
     *
     * @param AttributeRequirementInterface[] $requirements
     */
    public function setAttributeRequirements(array $requirements): \Akeneo\Pim\Structure\Component\Model\FamilyInterface;

    /**
     * Get attribute requirements
     *
     * @return AttributeRequirementInterface[]
     */
    public function getAttributeRequirements(): array;

    /**
     * Get grouped attributes
     *
     * @return AttributeInterface[]
     */
    public function getGroupedAttributes(): array;

    /**
     * Get label
     */
    public function getLabel(): string;

    /**
     * Set label
     *
     * @param string $label
     */
    public function setLabel(string $label): \Akeneo\Pim\Structure\Component\Model\FamilyInterface;

    /**
     * Get attribute requirement key
     *
     * @param AttributeRequirementInterface $requirement
     */
    public function getAttributeRequirementKey(AttributeRequirementInterface $requirement): string;

    public function getAttributeAsLabelChoices(): array;

    /**
     * @return Collection
     */
    public function getFamilyVariants(): Collection;

    /**
     * @param Collection $familyVariants
     */
    public function setFamilyVariants(Collection $familyVariants): void;
}
