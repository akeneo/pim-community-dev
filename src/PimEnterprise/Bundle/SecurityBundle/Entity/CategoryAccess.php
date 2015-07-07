<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Entity;

use Oro\Bundle\UserBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Model\CategoryAccessInterface;

/**
 * Category Access entity
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class CategoryAccess implements CategoryAccessInterface
{
    /** @var int */
    protected $id;

    /** @var CategoryInterface */
    protected $category;

    /** @var Group */
    protected $userGroup;

    /** @var bool */
    protected $viewProducts;

    /** @var bool */
    protected $editProducts;

    /** @var bool */
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
