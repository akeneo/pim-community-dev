<?php

namespace PimEnterprise\Bundle\SecurityBundle\Entity;

use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

/**
 * Category Ownership entity
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CategoryOwnership
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var Role $role
     */
    protected $role;

    /**
     * @var CategoryInterface $category
     */
    protected $category;

    /**
     * Get ID
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
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
     * @return CategoryOwnership
     */
    public function setRole(Role $role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get category
     *
     * @return CategoryInterface
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set category
     *
     * @param CategoryInterface $category
     *
     * @return CategoryOwnership
     */
    public function setCategory(CategoryInterface $category)
    {
        $this->category = $category;

        return $this;
    }
}
