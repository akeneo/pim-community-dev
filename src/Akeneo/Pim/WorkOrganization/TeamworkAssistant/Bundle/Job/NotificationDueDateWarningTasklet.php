<?php

declare(strict_types=1);

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Job;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Notification\ProjectNotifierInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProjectCompletenessRepositoryInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProjectRepositoryInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\UserRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 *  Sends a notification to users with a close due date.
 *
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotificationDueDateWarningTasklet implements TaskletInterface
{
    protected const JOB_CODE = 'project_notification_due_date';

    public function __construct(
        private ProjectRepositoryInterface $projectRepository,
        private ProjectNotifierInterface $projectNotifier,
        private ProjectCompletenessRepositoryInterface $projectCompletenessRepository,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
    }

    public function execute(): void
    {
        $projects = $this->projectRepository->findAll();

        foreach ($projects as $project) {
            $users = $this->userRepository->findUsersToNotify($project);
            foreach ($users as $user) {
                $projectCompleteness = $this->projectCompletenessRepository
                    ->getProjectCompleteness($project, $user);
                $this->projectNotifier->notifyUser($user, $project, $projectCompleteness);
            }
        }
    }
}
