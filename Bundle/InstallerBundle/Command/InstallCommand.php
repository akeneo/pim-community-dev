<?php

namespace Oro\Bundle\InstallerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class InstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('oro:install')
            ->setDescription('Oro Application Installer.')
            ->addOption('user-name', 'un', InputOption::VALUE_OPTIONAL, 'User name')
            ->addOption('user-email', 'ue', InputOption::VALUE_OPTIONAL, 'User email')
            ->addOption('user-firstname', 'ufn', InputOption::VALUE_OPTIONAL, 'User first name')
            ->addOption('user-lastname', 'uln', InputOption::VALUE_OPTIONAL, 'User last name')
            ->addOption('user-password', 'up', InputOption::VALUE_OPTIONAL, 'User password');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Installing Oro Application.</info>');
        $output->writeln('');

        $this
            ->checkStep($input, $output)
            ->setupStep($input, $output);

        $output->writeln('<info>Oro application has been successfully installed.</info>');
    }

    protected function checkStep(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Checking system requirements:</info>');

        $fulfilled = true;

        foreach ($this->getContainer()->get('oro_installer.requirements') as $collection) {
            $output->writeln(sprintf('<comment>%s</comment>', $collection->getLabel()));

            foreach ($collection as $requirement) {
                $output->write($requirement->getLabel());

                if ($requirement->isFulfilled()) {
                    $output->writeln(' <info>OK</info>');
                } else {
                    if ($requirement->isRequired()) {
                        $fulfilled = false;
                        $output->writeln(' <error>ERROR</error>');
                        $output->writeln(sprintf('<comment>%s</comment>', $requirement->getHelp()));
                    } else {
                        $output->writeln(' <comment>WARNING</comment>');
                    }
                }
            }
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
            ->runCommand('doctrine:schema:create', $input, $output)
            ->runCommand('doctrine:fixtures:load', $input, $output);

//        if ($dialog->askConfirmation($output, '<question>Load fixtures (Y/N)?</question>', false)) {
//            $this->runCommand('doctrine:fixtures:load', $input, $output);
//        }

        $output->writeln('');
        $output->writeln('<info>Administration setup.</info>');

        $options = $input->getOptions();
        $user    = $this->get('oro_user.manager')->createUser();
        $role    = $this
            ->getDoctrine()
            ->getRepository('OroUserBundle:Role')
            ->findOneBy(array('role' => 'ROLE_SUPER_ADMIN'));

        $user
            ->setUsername(
                isset($options['user-name'])
                    ? $options['user-name']
                    : $dialog->ask($output, '<question>Username:</question>')
            )
            ->setEmail(
                isset($options['user-email'])
                    ? $options['user-email']
                    : $dialog->ask($output, '<question>Email:</question>')
            )
            ->setFirstname(
                isset($options['user-firstname'])
                    ? $options['user-firstname']
                    : $dialog->ask($output, '<question>First name:</question>')
            )
            ->setLastname(
                isset($options['user-lastname'])
                    ? $options['user-lastname']
                    : $dialog->ask($output, '<question>Last name:</question>')
            )
            ->setPlainPassword(
                isset($options['user-password'])
                    ? $options['user-password']
                    : $dialog->ask($output, '<question>Password:</question>')
            )
            ->setEnabled(true)
            ->addRole($role);

        $this->get('oro_user.manager')->updateUser($user);

        $output->writeln('');

        return $this;
    }

    protected function finalStep(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Preparing application.</info>');

        $input->setInteractive(false);

        $this
            ->runCommand('oro:navigation:init', $input, $output)
            ->runCommand('oro:entity-config:update', $input, $output)
            ->runCommand('oro:entity-extend:init', $input, $output)
            ->runCommand('oro:entity-extend:create', $input, $output)
            ->runCommand('cache:clear', $input, $output)
//            ->runCommand('doctrine:schema:update', $input, $output) // array('--force' => true)
            ->runCommand('oro:search:create-index', $input, $output)
//            ->runCommand('assets:install', $input, $output) // array('target' => './')
            ->runCommand('assetic:dump', $input, $output)
            ->runCommand('oro:assetic:dump', $input, $output)
            ->runCommand('oro:translation:dump', $input, $output);

        $params = $this->get('oro_installer.yaml_persister')->parse();

        $params['system']['installed'] = date('c');

        $this->get('oro_installer.yaml_persister')->dump($params);

        $output->writeln('');

        return $this;
    }

    private function runCommand($command, InputInterface $input, OutputInterface $output)
    {
        $this
            ->getApplication()
            ->find($command)
            ->run($input, $output);

        return $this;
    }
}