<?php

namespace PimEnterprise\Bundle\SecurityBundle\Entity;

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
     * @var integer $attributeGroupId
     */
    protected $attributeGroupId;

    /**
     * @var integer $roleId
     */
    protected $roleId;

    /**
     * @var boolean $viewAttributes
     */
    protected $viewAttributes;

    /**
     * @var boolean $editAttributes
     */
    protected $editAttributes;

    /**
     * Get attribute group id
     *
     * @return integer
     */
    public function getAttributeGroupId()
    {
        return $this->attributeGroupId;
    }

    /**
     * Set attribute group id
     *
     * @param integer $attributeGroupId
     *
     * @return AttributeGroupAccess
     */
    public function setAttributeGroupId($attributeGroupId)
    {
        $this->attributeGroupId = $attributeGroupId;

        return $this;
    }

    /**
     * Get role id
     *
     * @return integer
     */
    public function getRoleId()
    {
        return $this->roleId;
    }

    /**
     * Set role id
     *
     * @param integer $roleId
     *
     * @return AttributeGroupAccess
     */
    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;

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
