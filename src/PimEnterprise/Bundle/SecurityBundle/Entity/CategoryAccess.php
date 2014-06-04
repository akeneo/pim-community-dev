<?php

namespace PimEnterprise\Bundle\SecurityBundle\Entity;

use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Model\CategoryAccessInterface;

/**
 * Category Access entity
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CategoryAccess implements CategoryAccessInterface
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var CategoryInterface $category
     */
    protected $category;

    /**
     * @var Role $role
     */
    protected $role;

    /**
     * @var boolean $viewProducts
     */
    protected $viewProducts;

    /**
     * @var boolean $editProducts
     */
    protected $editProducts;

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
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * {@inheritdoc}
     */
    public function setEditProducts($editProducts)
    {
        $this->editProducts = $editProducts;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEditProducts()
    {
        return $this->editProducts;
    }

    /**
     * {@inheritdoc}
     */
    public function setViewProducts($viewProducts)
    {
        $this->viewProducts = $viewProducts;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isViewProducts()
    {
        return $this->viewProducts;
    }
}
