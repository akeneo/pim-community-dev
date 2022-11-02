<?php

namespace Akeneo\UserManagement\Infrastructure\Cli;

use Akeneo\UserManagement\Application\Exception\UnknownUserRole;
use Akeneo\UserManagement\Application\RestoreAdminRolePermissions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class RestoreAdminRolePermissionsCommand extends Command
{
    protected static $defaultName = 'pim:user:restore-admin-permissions';

    public function __construct(private RestoreAdminRolePermissions $restoreAdminRolePermissions)
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Restore all permissions to the <info>ROLE_ADMINISTRATOR</info> user role')
            ->addOption(
                'create',
                'c',
                InputOption::VALUE_NONE,
                'Recreate the <info>ROLE_ADMINISTRATOR</info> user role if it does not exist'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $helper **/
        $helper = $this->getHelper('question');

        $question = new ConfirmationQuestion(
            '<question>You are about to restore all permissions to the ROLE_ADMINISTRATOR user role. Do you want to continue? [Y/n] </question>',
        );

        if (!$helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }

        try {
            ($this->restoreAdminRolePermissions)((bool) $input->getOption('create'));
        } catch (UnknownUserRole $exception) {
            $output->writeln('<error>The ROLE_ADMINISTRATOR user role does not exist</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>Permissions restored with success</info>');
        return Command::SUCCESS;
    }
}
