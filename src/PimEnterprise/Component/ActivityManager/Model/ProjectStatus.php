<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Model;

use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;

/**
 * @author Soulet Olivier <olivier.soulet@akeneo.com>
 */
class ProjectStatus implements ProjectStatusInterface
{
    /** @var int */
    protected $id;

    /** @var UserInterface */
    protected $user;

    /** @var ProjectInterface */
    protected $project;

    /** @var bool */
    protected $isComplete;

    /** @var bool */
    protected $hasBeenNotified;

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
    public function getUser()
    {
        return $this->user;
    }

    /**
     * {@inheritdoc}
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * {@inheritdoc}
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * {@inheritdoc}
     */
    public function setProject(ProjectInterface $project)
    {
        $this->project = $project;
    }

    /**
     * {@inheritdoc}
     */
    public function isComplete()
    {
        return $this->isComplete;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsComplete($isComplete)
    {
        $this->isComplete = $isComplete;
    }

    /**
     * {@inheritdoc}
     */
    public function hasBeenNotified()
    {
        return $this->hasBeenNotified;
    }

    /**
     * {@inheritdoc}
     */
    public function setHasBeenNotified($hasBeenNotified)
    {
        $this->hasBeenNotified = $hasBeenNotified;
    }
}
