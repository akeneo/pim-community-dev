<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TeamWorkAssistantBundle\Command;

use PimEnterprise\Component\TeamWorkAssistant\Notification\ProjectNotifierInterface;
use PimEnterprise\Component\TeamWorkAssistant\Repository\ProjectCompletenessRepositoryInterface;
use PimEnterprise\Component\TeamWorkAssistant\Repository\ProjectRepositoryInterface;
use PimEnterprise\Component\TeamWorkAssistant\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * It sends a notification to user to inform them the due date for a project is close.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class NotificationDueDateWarningCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pimee:project:notify-before-due-date')
            ->setDescription('Sends a notification to users with a close due date.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projects = $this->getProjectRepository()->findAll();

        foreach ($projects as $project) {
            $users = $this->getProjectUserRepository()->findUsersToNotify($project);
            foreach ($users as $user) {
                $projectCompleteness = $this->getProjectCompletenessRepository()
                    ->getProjectCompleteness($project, $user);
                $this->getNotifier()->notifyUser($user, $project, $projectCompleteness);
                $output->writeln(sprintf('User %s has been notified.', $user->getUsername()));
            }
        }

        return 0;
    }

    /**
     * @return ProjectRepositoryInterface
     */
    protected function getProjectRepository()
    {
        return $this->getContainer()->get('pimee_team_work_assistant.repository.project');
    }

    /**
     * @return ProjectNotifierInterface
     */
    protected function getNotifier()
    {
        return $this->getContainer()->get('pimee_team_work_assistant.notifier.project_due_date_reminder');
    }

    /**
     * @return ProjectCompletenessRepositoryInterface
     */
    protected function getProjectCompletenessRepository()
    {
        return $this->getContainer()->get('pimee_team_work_assistant.repository.project_completeness');
    }

    /**
     * @return UserRepositoryInterface
     */
    protected function getProjectUserRepository()
    {
        return $this->getContainer()->get('pimee_team_work_assistant.repository.user');
    }
}
