<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class Project implements ProjectInterface
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $code;

    /** @var string */
    protected $label;

    /** @var string */
    protected $description;

    /** @var \DateTime */
    protected $dueDate;

    /** @var UserInterface */
    protected $owner;

    /** @var DatagridView */
    protected $datagridView;

    /** @var ChannelInterface */
    protected $channel;

    /** @var LocaleInterface */
    protected $locale;

    /** @var ArrayCollection */
    protected $userGroups;

    /** @var string */
    protected $productFilters;

    /** @var ArrayCollection */
    protected $projectStatus;

    /** @var bool */
    protected $isCompletenessComputed;

    public function __construct()
    {
        $this->userGroups = new ArrayCollection();
        $this->projectStatus = new ArrayCollection();
        $this->isCompletenessComputed = false;
    }

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
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->code = (string)$code;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * {@inheritdoc}
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * {@inheritdoc}
     */
    public function setDueDate(\DateTime $dueDate = null)
    {
        $this->dueDate = $dueDate;
    }

    /**
     * {@inheritdoc}
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * {@inheritdoc}
     */
    public function setOwner(UserInterface $owner)
    {
        $this->owner = $owner;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * {@inheritdoc}
     */
    public function setChannel(ChannelInterface $channel)
    {
        $this->channel = $channel;
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
    }

    /**
     * {@inheritdoc}
     */
    public function getDatagridView()
    {
        return $this->datagridView;
    }

    /**
     * {@inheritdoc}
     */
    public function setDatagridView(DatagridView $datagridView)
    {
        $this->datagridView = $datagridView;
    }

    /**
     * {@inheritdoc}
     */
    public function addUserGroup(GroupInterface $userGroup)
    {
        if (!$this->userGroups->contains($userGroup)) {
            $this->userGroups[] = $userGroup;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeUserGroup(GroupInterface $userGroup)
    {
        $this->userGroups->removeElement($userGroup);
    }

    /**
     * {@inheritdoc}
     */
    public function resetUserGroups()
    {
        $this->userGroups = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserGroups()
    {
        return $this->userGroups;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductFilters()
    {
        return $this->productFilters;
    }

    /**
     * {@inheritdoc}
     */
    public function setProductFilters(array $productFilters)
    {
        $this->productFilters = $productFilters;
    }

    /**
     * {@inheritdoc}
     */
    public function addProjectStatus(ProjectStatusInterface $projectStatus)
    {
        if (!$this->projectStatus->contains($projectStatus)) {
            $this->projectStatus[] = $projectStatus;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resetProjectStatus()
    {
        $this->projectStatus = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectStatus()
    {
        return $this->projectStatus;
    }

    /**
     * {@inheritdoc}
     */
    public function isCompletenessComputed(): bool
    {
        return $this->isCompletenessComputed;
    }

    /**
     * {@inheritdoc}
     */
    public function startCompletenessComputing(): void
    {
        $this->isCompletenessComputed = false;
    }

    /**
     * {@inheritdoc}
     */
    public function endCompletenessComputing(): void
    {
        $this->isCompletenessComputed = true;
    }
}
