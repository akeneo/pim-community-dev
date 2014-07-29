<?php

namespace PimEnterprise\Bundle\SecurityBundle\Entity;

use Oro\Bundle\UserBundle\Entity\Group;
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
    /** @var integer */
    protected $id;

    /** @var CategoryInterface */
    protected $category;

    /** @var Group */
    protected $userGroup;

    /** @var boolean */
    protected $viewProducts;

    /** @var boolean */
    protected $editProducts;

    /** @var boolean */
    protected $ownProducts;

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
    public function setCategory(CategoryInterface $category)
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

    /**
     * {@inheritdoc}
     */
    public function setOwnProducts($ownProducts)
    {
        $this->ownProducts = $ownProducts;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isOwnProducts()
    {
        return $this->ownProducts;
    }
}
