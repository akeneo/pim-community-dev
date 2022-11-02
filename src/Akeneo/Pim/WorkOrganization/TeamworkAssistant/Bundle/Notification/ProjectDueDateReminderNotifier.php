<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Notification;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectCompleteness;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Notification\ProjectNotifierInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;

/**
 * Notify User when a project due date is close;
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectDueDateReminderNotifier implements ProjectNotifierInterface
{
    /** @var ProjectNotificationFactory */
    protected $projectNotificationFactory;

    /** @var NotifierInterface */
    protected $notifier;

    /** @var PresenterInterface */
    protected $datePresenter;

    /** @var array */
    protected $reminders;

    public function __construct(
        ProjectNotificationFactory $projectNotificationFactory,
        NotifierInterface $notifier,
        PresenterInterface $datePresenter,
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
            'buttonLabel' => 'teamwork_assistant.notification.due_date.start',
        ];

        $parameters =
            [
                '%project_label%' => $project->getLabel(),
                '%due_date%'      => $formattedDate,
                '%percent%'       => $projectCompleteness->getRatioForDone(),
            ];
        $routeParams = ['identifier' => $project->getCode(), 'status' => 'contributor-todo'];

        $message = $user->getUserIdentifier() === $project->getOwner()->getUserIdentifier()
            ? 'teamwork_assistant.notification.due_date.owner'
            : 'teamwork_assistant.notification.due_date.contributor';

        $notification = $this->projectNotificationFactory->create($routeParams, $parameters, $context, $message);
        $this->notifier->notify($notification, [$user]);

        return true;
    }

    /**
     * Checks if the number of days remaining before the due date are in the array
     *
     * @param \DateTime $dueDate
     *
     * @return bool
     */
    protected function checkDates(\DateTime $dueDate)
    {
        $dateOfTheDay = new \DateTime('today');

        $days = $dateOfTheDay->diff($dueDate)->format('%a');

        if (in_array($days, $this->reminders)) {
            return true;
        }

        return false;
    }
}
