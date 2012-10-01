<?php
namespace Bap\FlexibleEntityBundle\Model;

/**
 * Entity type
 *
 * @author Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class EntityType
{
    /**
     * Set unique code
     * @param string code
     */
    public function setTypeCode(string $code);

    /**
     * Get code
     * @return string code
     */
    public function getTypeCode();

    /**
     * Add a group
     * @param string code
     */
    public function addAttributeGroup(string $code);

    /**
     * Get a group
     * @param string code
     * @return ???
     */
    public function getAttributeGroup(string $code);

    /**
     * Remove a group
     * @param string code
     * @return ???
     */
    public function removeAttributeGroup(string $code, boolean $forceIfNotEmpty = false);

    /**
     * Add an attribute
     */
    public function addAttribute(string $code, FieldTypeInterface $type, mixed $isMultivalue, $fieldGroup);

    /**
     * Remove an attribute
     */
    public function removeAttribute(string $code);

    /**
     * Create new flexible entity instance
     */
    public function newFlexibleEntityInstance();

}