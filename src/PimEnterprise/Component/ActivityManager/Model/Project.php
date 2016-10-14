<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Component\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\UserBundle\Entity\Group;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use Pim\Component\Catalog\Model\AttributeGroupInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class Project implements ProjectInterface
{
    /** @var int */
    private $id;

    /** @var string */
    private $label;

    /** @var string */
    private $description;

    /** @var \DateTime */
    private $dueDate;

    /** @var ArrayCollection */
    private $datagridViews;

    /** @var ArrayCollection */
    private $userGroups;

    public function __construct()
    {
        $this->datagridViews = new ArrayCollection();
        $this->userGroups = new ArrayCollection();
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
    public function getDatagridViews()
    {
        return $this->datagridViews;
    }

    /**
     * {@inheritdoc}
     */
    public function addDatagridView(DatagridView $datagridView)
    {
        $this->datagridViews[] = $datagridView;
    }

    /**
     * {@inheritdoc}
     */
    public function removeDatagridView(DatagridView $datagridView)
    {
        $this->datagridViews->removeElement($datagridView);
    }

    public function addUserGroup(Group $userGroup)
    {
        if (!$this->userGroups->contains($userGroup)) {
            $this->userGroups[] = $userGroup;
        }
    }
}
