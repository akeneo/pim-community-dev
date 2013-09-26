<?php

namespace Oro\Bundle\InstallerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;

class InstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('oro:install')
            ->setDescription('Oro Application Installer.')
            ->addOption('user-name', null, InputOption::VALUE_OPTIONAL, 'User name')
            ->addOption('user-email', null, InputOption::VALUE_OPTIONAL, 'User email')
            ->addOption('user-firstname', null, InputOption::VALUE_OPTIONAL, 'User first name')
            ->addOption('user-lastname', null, InputOption::VALUE_OPTIONAL, 'User last name')
            ->addOption('user-password', null, InputOption::VALUE_OPTIONAL, 'User password');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Installing Oro Application.</info>');
        $output->writeln('');

        $this
            ->checkStep($input, $output)
            ->setupStep($input, $output)
            ->finalStep($input, $output);

        $output->writeln('<info>Oro Application has been successfully installed.</info>');
    }

    protected function checkStep(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Checking system requirements:</info>');

        $table     = $this->getHelperSet()->get('table');
        $fulfilled = true;

        foreach ($this->getContainer()->get('oro_installer.requirements') as $collection) {
            $table->setHeaders(array(sprintf('%1$-30s', $collection->getLabel()), 'Check     '));

            $rows = array();

            foreach ($collection as $requirement) {
                $row = array($requirement->getLabel());

                if ($requirement->isFulfilled()) {
                    $row[] = 'OK';
                } else {
                    if ($requirement->isRequired()) {
                        $fulfilled = false;

                        $row[] = 'ERROR'; // $requirement->getHelp()
                    } else {
                        $row[] = 'WARNING';
                    }
                }

                $rows[] = $row;
            }

            $table
                ->setRows($rows)
                ->render($output);
        }

        if (!$fulfilled) {
            throw new \RuntimeException('Some system requirements are not fulfilled. Please check output messages and fix them.');
        }

        $output->writeln('');

        return $this;
    }

    protected function setupStep(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Setting up database.</info>');

        $dialog = $this->getHelperSet()->get('dialog');

        $input->setInteractive(false);

        $this
            ->runCommand('oro:entity-extend:clear', $output)
            ->runCommand('doctrine:schema:drop', $output, array('--force' => true, '--full-database' => true))
            ->runCommand('doctrine:schema:create', $output)
            ->runCommand('doctrine:fixtures:load', $output, array('--no-interaction' => true));

        $output->writeln('');
        $output->writeln('<info>Administration setup.</info>');

        $options = $input->getOptions();
        $user    = $this->getContainer()->get('oro_user.manager')->createUser();
        $role    = $this
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('OroUserBundle:Role')
            ->findOneBy(array('role' => 'ROLE_SUPER_ADMIN'));

        $user
            ->setUsername(
                isset($options['user-name'])
                    ? $options['user-name']
                    : $dialog->ask($output, '<question>Username:</question> ')
            )
            ->setEmail(
                isset($options['user-email'])
                    ? $options['user-email']
                    : $dialog->ask($output, '<question>Email:</question> ')
            )
            ->setFirstname(
                isset($options['user-firstname'])
                    ? $options['user-firstname']
                    : $dialog->ask($output, '<question>First name:</question> ')
            )
            ->setLastname(
                isset($options['user-lastname'])
                    ? $options['user-lastname']
                    : $dialog->ask($output, '<question>Last name:</question> ')
            )
            ->setPlainPassword(
                isset($options['user-password'])
                    ? $options['user-password']
                    : $dialog->askHiddenResponse($output, '<question>Password:</question> ')
            )
            ->setEnabled(true)
            ->addRole($role);

        $this->getContainer()->get('oro_user.manager')->updateUser($user);

        $output->writeln('');

        return $this;
    }

    protected function finalStep(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Preparing application.</info>');

        $input->setInteractive(false);

        $this
            ->runCommand('oro:entity-config:init', $output)
            ->runCommand('oro:entity-extend:init', $output)
            ->runCommand('oro:entity-extend:update-config', $output)
            ->runCommand('doctrine:schema:update', $output, array('--force' => true, '--no-interaction' => true))
            ->runCommand('oro:search:create-index', $output)
            ->runCommand('oro:navigation:init', $output)
            ->runCommand('assets:install', $output, array('target' => './'))
            ->runCommand('assetic:dump', $output)
            ->runCommand('oro:assetic:dump', $output)
            ->runCommand('oro:translation:dump', $output);

        $params = $this->getContainer()->get('oro_installer.yaml_persister')->parse();

        $params['system']['installed'] = date('c');

        $this->getContainer()->get('oro_installer.yaml_persister')->dump($params);

        $output->writeln('');

        return $this;
    }

    private function runCommand($command, OutputInterface $output, $params = array())
    {
        $params = array_merge(
            array(
                'command'    => $command,
                '--no-debug' => true,
            ),
            $params
        );

        $this->getApplication()->setAutoExit(false);
        $this->getApplication()->run(new ArrayInput($params), $output);

        return $this;
    }
}
