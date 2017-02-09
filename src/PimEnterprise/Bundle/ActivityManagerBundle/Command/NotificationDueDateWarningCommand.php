<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ActivityManagerBundle\Command;

use PimEnterprise\Bundle\ActivityManagerBundle\Notification\ProjectDueDateNotifierInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectCompletenessRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\ProjectRepositoryInterface;
use PimEnterprise\Component\ActivityManager\Repository\UserRepositoryInterface;
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
        return $this->getContainer()->get('pimee_activity_manager.repository.project');
    }

    /**
     * @return ProjectDueDateNotifierInterface
     */
    protected function getNotifier()
    {
        return $this->getContainer()->get('pimee_activity_manager.notifier.project_due_date');
    }

    /**
     * @return ProjectCompletenessRepositoryInterface
     */
    protected function getProjectCompletenessRepository()
    {
        return $this->getContainer()->get('pimee_activity_manager.repository.project_completeness');
    }

    /**
     * @return UserRepositoryInterface
     */
    protected function getProjectUserRepository()
    {
        return $this->getContainer()->get('pimee_activity_manager.repository.user');
    }
}
