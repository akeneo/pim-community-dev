<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Command;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Notification\ProjectNotifierInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProjectCompletenessRepositoryInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProjectRepositoryInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\UserRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * It sends a notification to user to inform them the due date for a project is close.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class NotificationDueDateWarningCommand extends Command
{
    protected static $defaultName = 'pimee:project:notify-before-due-date';

    /** @var ProjectRepositoryInterface */
    private $projectRepository;

    /** @var ProjectNotifierInterface */
    private $projectNotifier;

    /** @var ProjectCompletenessRepositoryInterface */
    private $projectCompletenessRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(
        ProjectRepositoryInterface $projectRepository,
        ProjectNotifierInterface $projectNotifier,
        ProjectCompletenessRepositoryInterface $projectCompletenessRepository,
        UserRepositoryInterface $userRepository
    ) {
        parent::__construct();

        $this->projectRepository = $projectRepository;
        $this->projectNotifier = $projectNotifier;
        $this->projectCompletenessRepository = $projectCompletenessRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Sends a notification to users with a close due date.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $projects = $this->projectRepository->findAll();

        foreach ($projects as $project) {
            $users = $this->userRepository->findUsersToNotify($project);
            foreach ($users as $user) {
                $projectCompleteness = $this->projectCompletenessRepository
                    ->getProjectCompleteness($project, $user);
                $this->projectNotifier->notifyUser($user, $project, $projectCompleteness);
                $output->writeln(sprintf('User %s has been notified.', $user->getUsername()));
            }
        }

        return 0;
    }
}
