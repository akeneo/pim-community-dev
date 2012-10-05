<?php
namespace Bap\FlexibleEntityBundle\Model;

/**
 * Define an entity type, by exemple, for a product, a t-shirt type which
 * contains specific fields
 *
 * @author     Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class EntityType
{
    /**
     * @var string $code
     */
    protected $code;

    /**
     * Get code
     * @return string code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set unique code
     *
     * @param string code
     * @return EntityType
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Add a group
     * @param string code
     */
    //public abstract function addAttributeGroup(string $code);

    /**
     * Get a group
     * @param string code
     * @return ???
     */
    //public abstract function getAttributeGroup(string $code);

    /**
     * Remove a group
     * @param string code
     * @return ???
     */
    //public abstract function removeAttributeGroup(string $code, boolean $forceIfNotEmpty = null);

    /**
     * Add an attribute
     */
    //public  abstract function addAttribute(string $code, FieldTypeInterface $type, mixed $isMultivalue, $fieldGroup);

    /**
     * Remove an attribute
     */
    //public  abstract function removeAttribute(string $code);

    /**
     * Create new flexible entity instance
     */
    //public  abstract function newFlexibleEntityInstance();

}