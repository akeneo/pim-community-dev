<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Pim\Bundle\TranslationBundle\Entity\TranslatableInterface;
use Pim\Bundle\VersioningBundle\Model\VersionableInterface;

/**
 * Family interface
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FamilyInterface extends TranslatableInterface, ReferableInterface, VersionableInterface
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
     * @return AttributeInterface[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getAttributes();

    /**
     * @param AttributeInterface $attribute
     *
     * @return FamilyInterface
     */
    public function hasAttribute(AttributeInterface $attribute);

    /**
     * @param AttributeInterface $attributeAsLabel
     *
     * @return FamilyInterface
     */
    public function setAttributeAsLabel(AttributeInterface $attributeAsLabel);

    /**
     * @return AttributeInterface
     */
    public function getAttributeAsLabel();

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
     * Set attribute requirements
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
     * Get indexed attribute requirements
     *
     * @return AttributeRequirementInterface[]
     */
    public function getIndexedAttributeRequirements();

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
}
