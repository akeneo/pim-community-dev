<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Notification;

use Pim\Bundle\NotificationBundle\NotifierInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Notify User for project finished.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectFinishedNotifier implements ProjectNotifierInterface
{
    /** @var ProjectFinishedNotificationFactory */
    protected $projectFinishedNotificationFactory;

    /** @var NotifierInterface */
    protected $notifier;

    /**
     * @param ProjectFinishedNotificationFactory $projectFinishedNotificationFactory
     * @param NotifierInterface                           $notifier
     */
    public function __construct(
        ProjectFinishedNotificationFactory $projectFinishedNotificationFactory,
        NotifierInterface $notifier
    ) {
        $this->projectFinishedNotificationFactory = $projectFinishedNotificationFactory;
        $this->notifier = $notifier;
    }

    /**
     * {@inheritdoc}
     */
    public function notifyUser(UserInterface $user, ProjectInterface $project)
    {
        $message = $user->getUsername() === $project->getOwner()->getUsername()
            ? 'activity_manager.notification.project_finished.owner'
            : 'activity_manager.notification.project_finished.contributor';

        $notification = $this->projectFinishedNotificationFactory->create($project, $message);

        $this->notifier->notify($notification, [$user]);
    }
}
