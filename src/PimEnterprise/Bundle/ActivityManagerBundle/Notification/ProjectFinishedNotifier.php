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

use Pim\Bundle\NotificationBundle\Notifier;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Notify User for project finished.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectFinishedNotifier implements NotifierInterface
{
    /** @var ProjectFinishedNotificationFactory */
    protected $projectFinishedNotificationFactory;

    /** @var Notifier */
    protected $notifier;

    /**
     * @param ProjectFinishedNotificationFactory $projectFinishedNotificationFactory
     * @param Notifier                           $notifier
     */
    public function __construct(
        ProjectFinishedNotificationFactory $projectFinishedNotificationFactory,
        Notifier $notifier
    ) {
        $this->projectFinishedNotificationFactory = $projectFinishedNotificationFactory;
        $this->notifier = $notifier;
    }

    /**
     * {@inheritdoc}
     */
    public function notifyUser(UserInterface $user, ProjectInterface $project)
    {
        $message = 'activity_manager.notification.project_finished.contributor';

        if ($user->getUsername() === $project->getOwner()->getUsername()) {
            $message = 'activity_manager.notification.project_finished.owner';
        }

        $notification = $this->projectFinishedNotificationFactory
            ->create($project, $message);

        $this->notifier->notify($notification, [$user]);
    }
}
