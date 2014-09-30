<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Pim\Bundle\CatalogBundle\Entity\AttributeRequirement;

/**
 * Family interface
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FamilyInterface
{
    /**
     * Get id
     *
     * @return integer
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
     * @return AttributeInterface[]|ArrayCollection
     */
    public function getAttributes();

    /**
     * @param AttributeInterface $attribute
     *
     * @return FamilyInterface
     */
    public function hasAttribute(AttributeInterface $attribute);


    /**
     * @param $attributeAsLabel
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
     * @param AttributeRequirement $requirement
     *
     * @return FamilyInterface
     */
    public function addAttributeRequirement(AttributeRequirement $requirement);

    /**
     * Set attribute requirements
     *
     * @param array $requirements
     *
     * @return FamilyInterface
     */
    public function setAttributeRequirements(array $requirements);

    /**
     * Get attribute requirements
     *
     * @return array
     */
    public function getAttributeRequirements();
}
