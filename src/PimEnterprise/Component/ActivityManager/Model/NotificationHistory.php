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
class NotificationHistory implements NotificationHistoryInterface
{
    /** @var int */
    protected $id;

    /** @var UserInterface */
    protected $user;

    /** @var ProjectInterface */
    protected $project;

    /** @var bool */
    protected $notificationProjectCreation;

    /** @var bool */
    protected $notificationProjectFinished;

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
    public function isNotificationProjectFinished()
    {
        return $this->notificationProjectFinished;
    }

    /**
     * {@inheritdoc}
     */
    public function setNotificationProjectFinished($notificationProjectFinished)
    {
        $this->notificationProjectFinished = $notificationProjectFinished;
    }

    /**
     * {@inheritdoc}
     */
    public function isNotificationProjectCreation()
    {
        return $this->notificationProjectCreation;
    }

    /**
     * {@inheritdoc}
     */
    public function setNotificationProjectCreation($notificationProjectCreation)
    {
        $this->notificationProjectCreation = $notificationProjectCreation;
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
}
