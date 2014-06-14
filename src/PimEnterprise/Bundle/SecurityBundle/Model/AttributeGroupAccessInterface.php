<?php

namespace PimEnterprise\Bundle\SecurityBundle\Model;

use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;

/**
 * Attribute group access interface
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
interface AttributeGroupAccessInterface extends AccessInterface
{
    /**
     * Get attribute group
     *
     * @return AttributeGroup
     */
    public function getAttributeGroup();

    /**
     * Set attribute group
     *
     * @param AttributeGroup $attributeGroup
     *
     * @return AttributeGroupAccessInterface
     */
    public function setAttributeGroup(AttributeGroup $attributeGroup);

    /**
     * Predicate for view atttributes permission
     *
     * @return boolean
     */
    public function isViewAttributes();

    /**
     * Set view atttributes permission
     *
     * @param boolean $viewAttributes
     *
     * @return AttributeGroupAccessInterface
     */
    public function setViewAttributes($viewAttributes);

    /**
     * Predicate for edit atttributes permission
     *
     * @return boolean
     */
    public function isEditAttributes();

    /**
     * Set edit atttributes permission
     *
     * @param boolean $editAttributes
     *
     * @return AttributeGroupAccessInterface
     */
    public function setEditAttributes($editAttributes);
}
