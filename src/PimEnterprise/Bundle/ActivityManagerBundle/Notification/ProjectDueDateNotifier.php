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
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Notify User when a project due date is close;
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectDueDateNotifier implements ProjectDueDateNotifierInterface
{
    /** @var ProjectNotificationFactory */
    protected $projectNotificationFactory;

    /** @var NotifierInterface */
    protected $notifier;

    /** @var DatePresenter */
    protected $datePresenter;

    /** @var array */
    protected $reminders;

    /**
     * @param ProjectNotificationFactory $projectNotificationFactory
     * @param NotifierInterface          $notifier
     * @param DatePresenter              $datePresenter
     * @param array                      $reminders
     */
    public function __construct(
        ProjectNotificationFactory $projectNotificationFactory,
        NotifierInterface $notifier,
        DatePresenter $datePresenter,
        array $reminders
    ) {
        $this->projectNotificationFactory = $projectNotificationFactory;
        $this->notifier = $notifier;
        $this->datePresenter = $datePresenter;
        $this->reminders = $reminders;
    }

    /**
     * {@inheritdoc}
     */
    public function notifyUser(
        UserInterface $user,
        ProjectInterface $project,
        ProjectCompleteness $projectCompleteness
    ) {
        if ($projectCompleteness->isComplete() || !$this->checkDates($project->getDueDate())) {
            return false;
        }

        $userLocale = $user->getUiLocale();
        $formattedDate = $this->datePresenter->present(
            $project->getDueDate(),
            ['locale' => $userLocale->getCode()]
        );

        $context = [
            'actionType'  => 'project_due_date',
            'buttonLabel' => 'activity_manager.notification.due_date.start',
        ];

        $parameters =
            [
                '%project_label%' => $project->getLabel(),
                '%due_date%'      => $formattedDate,
                '%percent%'       => $projectCompleteness->getRatioForDone(),
            ];
        $routeParams = ['identifier' => $project->getCode()];

        $message = $user->getUsername() === $project->getOwner()->getUsername()
            ? 'activity_manager.notification.due_date.owner'
            : 'activity_manager.notification.due_date.contributor';

        $notification = $this->projectNotificationFactory->create($routeParams, $parameters, $context, $message);
        $this->notifier->notify($notification, [$user]);

        return true;
    }

    /**
     * @param \DateTime $dueDate
     *
     * @return bool
     */
    protected function checkDates(\DateTime $dueDate)
    {
        $dateOfTheDay = new \DateTime();

        $datetime1 = strtotime($dateOfTheDay->format('Y-m-d'));
        $datetime2 = strtotime($dueDate->format('Y-m-d'));

        $secs = $datetime2 - $datetime1;
        $days = $secs / 86400;

        if (in_array($days, $this->reminders)) {
            return true;
        }

        return false;
    }
}
