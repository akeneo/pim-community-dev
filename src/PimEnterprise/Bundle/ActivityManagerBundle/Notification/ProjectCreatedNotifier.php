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
 * Notify users for project created.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectCreatedNotifier implements NotifierInterface
{
    /** @var ProjectCreatedNotificationFactory */
    protected $projectCreatedNotificationFactory;

    /** @var Notifier */
    protected $notifier;

    /**
     * @param ProjectCreatedNotificationFactory $projectCreatedNotificationFactory
     * @param Notifier                          $notifier
     */
    public function __construct(
        ProjectCreatedNotificationFactory $projectCreatedNotificationFactory,
        Notifier $notifier
    ) {
        $this->projectCreatedNotificationFactory = $projectCreatedNotificationFactory;
        $this->notifier = $notifier;
    }

    /**
     * {@inheritdoc}
     */
    public function notifyUser(UserInterface $user, ProjectInterface $project)
    {
        if ($user->getUsername() !== $project->getOwner()->getUsername()) {
            $notification = $this->projectCreatedNotificationFactory->create($project, $user);
            $this->notifier->notify($notification, [$user]);
        }
    }
}
