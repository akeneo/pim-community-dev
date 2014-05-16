<?php

namespace PimEnterprise\Bundle\SecurityBundle\Entity;

use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;

/**
 * Attribute Group Access entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeGroupAccess
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var AttributeGroup $attributeGroup
     */
    protected $attributeGroup;

    /**
     * @var Role $role
     */
    protected $role;

    /**
     * @var boolean $viewAttributes
     */
    protected $viewAttributes;

    /**
     * @var boolean $editAttributes
     */
    protected $editAttributes;

    /**
     * Get attribute group
     *
     * @return AttributeGroup
     */
    public function getAttributeGroup()
    {
        return $this->attributeGroup;
    }

    /**
     * Set attribute group
     *
     * @param AttributeGroup $attributeGroup
     *
     * @return AttributeGroupAccess
     */
    public function setAttributeGroup(AttributeGroup $attributeGroup)
    {
        $this->attributeGroup = $attributeGroup;

        return $this;
    }

    /**
     * Get role
     *
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set role
     *
     * @param Role $role
     *
     * @return AttributeGroupAccess
     */
    public function setRole(Role $role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get view atttributes permission
     *
     * @return boolean
     */
    public function getViewAttributes()
    {
        return $this->viewAttributes;
    }

    /**
     * Set view atttributes permission
     *
     * @param boolean $viewAttributes
     *
     * @return AttributeGroupAccess
     */
    public function setViewAttributes($viewAttributes)
    {
        $this->viewAttributes = $viewAttributes;

        return $this;
    }

    /**
     * Get edit atttributes permission
     *
     * @return boolean
     */
    public function getEditAttributes()
    {
        return $this->editAttributes;
    }

    /**
     * Set edit atttributes permission
     *
     * @param boolean $editAttributes
     *
     * @return AttributeGroupAccess
     */
    public function setEditAttributes($editAttributes)
    {
        $this->editAttributes = $editAttributes;

        return $this;
    }
}
