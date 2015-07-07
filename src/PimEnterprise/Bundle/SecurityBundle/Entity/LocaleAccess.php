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
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use PimEnterprise\Bundle\SecurityBundle\Model\LocaleAccessInterface;

/**
 * Locale Access entity
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class LocaleAccess implements LocaleAccessInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var LocaleInterface
     */
    protected $locale;

    /** @var Group */
    protected $userGroup;

    /**
     * @var bool
     */
    protected $viewProducts;

    /**
     * @var bool
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
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale(LocaleInterface $locale)
    {
        $this->locale = $locale;

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
    public function isViewProducts()
    {
        return $this->viewProducts;
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
    public function isEditProducts()
    {
        return $this->editProducts;
    }

    /**
     * {@inheritdoc}
     */
    public function setEditProducts($editProducts)
    {
        $this->editProducts = $editProducts;

        return $this;
    }
}
