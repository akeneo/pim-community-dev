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

use Akeneo\Component\Localization\Presenter\DatePresenter;
use Pim\Bundle\NotificationBundle\NotifierInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectCompleteness;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectStatusInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Notify User for project finished.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectFinishedNotifier implements ProjectNotifierInterface
{
    /** @var ProjectNotificationFactory */
    protected $projectNotificationFactory;

    /** @var NotifierInterface */
    protected $notifier;

    /** @var DatePresenter */
    protected $datePresenter;

    /**
     * @param ProjectNotificationFactory $projectNotificationFactory
     * @param NotifierInterface          $notifier
     * @param DatePresenter              $datePresenter
     */
    public function __construct(
        ProjectNotificationFactory $projectNotificationFactory,
        NotifierInterface $notifier,
        DatePresenter $datePresenter
    ) {
        $this->projectNotificationFactory = $projectNotificationFactory;
        $this->notifier = $notifier;
        $this->datePresenter = $datePresenter;
    }

    /**
     * {@inheritdoc}
     */
    public function notifyUser(
        UserInterface $user,
        ProjectInterface $project,
        ProjectStatusInterface $projectStatus,
        ProjectCompleteness $projectCompleteness
    ) {
        if ($projectCompleteness->isComplete() && !$projectStatus->isComplete()) {
            $userLocale = $project->getOwner()->getUiLocale();
            $formattedDate = $this->datePresenter->present(
                $project->getDueDate(),
                ['locale' => $userLocale->getCode()]
            );

            $parameters = [
                '%project_label%' => '"'.$project->getLabel().'"',
                '%due_date%'      => '"'.$formattedDate.'"',
                'project_code'    => $project->getCode(),
            ];

            $message = $user->getUsername() === $project->getOwner()->getUsername()
                ? 'activity_manager.notification.project_finished.owner'
                : 'activity_manager.notification.project_finished.contributor';

            $context = [
                'actionType'  => 'project_finished',
                'buttonLabel' => 'activity_manager.notification.project_finished.show',
            ];

            $notification = $this->projectNotificationFactory->create(
                ['identifier' => $parameters['project_code']],
                $parameters,
                $context,
                $message
            );

            $this->notifier->notify($notification, [$user]);

            return true;
        }

        return false;
    }
}
