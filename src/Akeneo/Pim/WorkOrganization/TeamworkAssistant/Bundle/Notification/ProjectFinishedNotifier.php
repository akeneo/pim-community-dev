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
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProjectStatusRepositoryInterface;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Webmozart\Assert\Assert;

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

    /** @var PresenterInterface */
    protected $datePresenter;

    /** @var ProjectStatusRepositoryInterface */
    protected $projectStatusRepository;

    public function __construct(
        ProjectNotificationFactory $projectNotificationFactory,
        NotifierInterface $notifier,
        PresenterInterface $datePresenter,
        ProjectStatusRepositoryInterface $projectStatusRepository
    ) {
        $this->projectNotificationFactory = $projectNotificationFactory;
        $this->notifier = $notifier;
        $this->datePresenter = $datePresenter;
        $this->projectStatusRepository = $projectStatusRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function notifyUser(
        UserInterface $user,
        ProjectInterface $project,
        ProjectCompleteness $projectCompleteness
    ) {
        $projectStatus = $this->projectStatusRepository->findProjectStatus($project, $user);
        if ($projectCompleteness->isComplete() && !$projectStatus->isComplete()) {
            $owner = $project->getOwner();
            Assert::implementsInterface($owner, UserInterface::class);
            $userLocale = $owner->getUiLocale();
            $formattedDate = $this->datePresenter->present(
                $project->getDueDate(),
                ['locale' => $userLocale->getCode()]
            );

            $parameters = [
                '%project_label%' => '"'.$project->getLabel().'"',
                '%due_date%'      => '"'.$formattedDate.'"',
                'project_code'    => $project->getCode(),
            ];

            $message = $user->getUserIdentifier() === $project->getOwner()->getUserIdentifier()
                ? 'teamwork_assistant.notification.project_finished.owner'
                : 'teamwork_assistant.notification.project_finished.contributor';

            $context = [
                'actionType'  => 'project_finished',
                'buttonLabel' => 'teamwork_assistant.notification.project_finished.show',
            ];

            $notification = $this->projectNotificationFactory->create(
                ['identifier' => $parameters['project_code'], 'status' => 'all'],
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
