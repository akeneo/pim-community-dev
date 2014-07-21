<?php

namespace PimEnterprise\Bundle\SecurityBundle\Entity;

use Oro\Bundle\UserBundle\Entity\Group;
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
    /** @var integer */
    protected $id;

    /** @var AttributeGroup */
    protected $attributeGroup;

    /** @var Group */
    protected $userGroup;

    /** @var boolean */
    protected $viewAttributes;

    /** @var boolean */
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
    public function getUserGroup()
    {
        return $this->userGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function setUserGroup(Group $group)
    {
        $this->userGroup = $group;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isViewAttributes()
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
    public function isEditAttributes()
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
