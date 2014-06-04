<?php

namespace PimEnterprise\Bundle\SecurityBundle\Entity;

use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use PimEnterprise\Bundle\SecurityBundle\Model\AttributeGroupAccessInterface;

/**
 * Attribute Group Access entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeGroupAccess implements AttributeGroupAccessInterface
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
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeGroup()
    {
        return $this->attributeGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeGroup(AttributeGroup $attributeGroup)
    {
        $this->attributeGroup = $attributeGroup;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * {@inheritdoc}
     */
    public function setRole(Role $role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getViewAttributes()
    {
        return $this->viewAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function setViewAttributes($viewAttributes)
    {
        $this->viewAttributes = $viewAttributes;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEditAttributes()
    {
        return $this->editAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function setEditAttributes($editAttributes)
    {
        $this->editAttributes = $editAttributes;

        return $this;
    }
}
