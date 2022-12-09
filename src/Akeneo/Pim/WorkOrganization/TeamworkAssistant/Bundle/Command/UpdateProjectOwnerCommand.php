<?php

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Command;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateProjectOwnerCommand extends Command
{
    protected static $defaultName = 'pimee:project:update-owner';
    protected static $defaultDescription = 'Update project ownership.';

    public function __construct(
        private IdentifiableObjectRepositoryInterface $userRepository,
        private ProjectRepositoryInterface $projectRepository,
        private ObjectUpdaterInterface $projectUpdater,
        private SaverInterface $projectSaver,
        private ObjectUpdaterInterface $datagridViewUpdater,
        private ValidatorInterface $validator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(
            [
                'Update project ownership',
                '========================',
            ],
            OutputInterface::VERBOSITY_VERBOSE
        );

        $projectId = $input->getArgument('project');
        $projectId = intval($projectId);
        if (!$projectId) {
            throw new InvalidArgumentException('The project argument has to be an identifier.');
        }

        $username = $input->getArgument('owner');
        if (!is_string($username)) {
            throw new InvalidArgumentException('The owner argument has to be a username.');
        }

        $project = $this->projectRepository->findOneBy(['id' => $projectId]);
        if (null === $project) {
            throw new InvalidArgumentException('The project is not found. Are your sure the project exists ?');
        }
        $output->writeln(
            sprintf("<comment> - Project found : %s (%d)</comment>", $project->getCode(), $project->getId()),
            OutputInterface::VERBOSITY_VERBOSE
        );

        $user = $this->userRepository->findOneByIdentifier($username);
        if (null === $user) {
            throw new InvalidArgumentException('The user is not found. Are your sure the user exists ?');
        }
        $output->writeln(
            sprintf("<comment> - User found : %s (%d)</comment>", $user->getUserIdentifier(), $user->getId()),
            OutputInterface::VERBOSITY_VERBOSE
        );

        $project = $this->updateProjectOwner($project, $user);
        $violations = $this->validator->validate($project);
        if (0 !== $violations->count()) {
            $output->writeln("<error> - the project is invalid. Impossible to update it.</error>");

            return Command::FAILURE;
        }
        $this->projectSaver->save($project);

        $output->writeln(
            sprintf("<comment> - Project updated : %s (%d)</comment>", $project->getCode(), $project->getId()),
            OutputInterface::VERBOSITY_VERBOSE
        );
        $output->writeln(sprintf(
            "%s<info>The project %d (%s) has been transferred to %s user.</info>",
            PHP_EOL,
            $project->getId(),
            $project->getCode(),
            $username
        ));

        return Command::SUCCESS;
    }

    private function updateProjectOwner(
        ProjectInterface $project,
        UserInterface $user,
    ): ProjectInterface {
        $datagridView = $project->getDatagridView();
        $this->datagridViewUpdater->update($datagridView, ['owner' => $user->getUserIdentifier()]);
        $this->projectUpdater->update($project, [
            'datagrid_view' => $datagridView,
            'owner' => $user->getUserIdentifier()
        ]);

        return $project;
    }

    protected function configure()
    {
        $this
            ->setHelp('This command allows you to update the project ownership and his associated views.')
            ->addArgument(
                'project',
                InputArgument::REQUIRED,
                'The project identifier to update.'
            )
            ->addArgument(
                'owner',
                InputArgument::REQUIRED,
                'the username of the user who can manage the project and his view.'
            );
    }
}
