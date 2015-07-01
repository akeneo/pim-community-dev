<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Entity;

use Oro\Bundle\UserBundle\Entity\Group;
use Pim\Component\Classification\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Model\CategoryAccessInterface;

/**
 * Asset Category Access entity
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class AssetCategoryAccess implements CategoryAccessInterface
{
    /** @var int */
    protected $id;

    /** @var CategoryInterface */
    protected $category;

    /** @var Group */
    protected $userGroup;

    /** @var bool */
    protected $viewAssets;

    /** @var bool */
    protected $editAssets;

    /** @var bool */
    protected $ownAssets;

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
    public function setEditItems($editItems)
    {
        $this->editAssets = $editItems;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEditItems()
    {
        return $this->editAssets;
    }

    /**
     * {@inheritdoc}
     */
    public function setViewItems($viewItems)
    {
        $this->viewAssets = $viewItems;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isViewItems()
    {
        return $this->viewAssets;
    }

    /**
     * {@inheritdoc}
     */
    public function setOwnItems($ownItems)
    {
        $this->ownAssets = $ownItems;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isOwnItems()
    {
        return $this->ownAssets;
    }
}
