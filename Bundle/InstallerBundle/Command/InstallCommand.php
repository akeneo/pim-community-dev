<?php

namespace Oro\Bundle\InstallerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;

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
            ->addOption('user-password', null, InputOption::VALUE_OPTIONAL, 'User password')
            ->addOption('sample-data', null, InputOption::VALUE_OPTIONAL, 'Load sample data');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->getContainer()->hasParameter('installed') && $this->getContainer()->getParameter('installed')) {
            throw new \RuntimeException('Oro Application already installed.');
        }

        $output->writeln('<info>Installing Oro Application.</info>');
        $output->writeln('');

        $this
            ->checkStep($input, $output)
            ->setupStep($input, $output)
            ->finalStep($input, $output);

        $output->writeln('');
        $output->writeln('<info>Oro Application has been successfully installed.</info>');
    }

    protected function checkStep(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Oro requirements check:</info>');

        if (!class_exists('OroRequirements')) {
            require_once $this->getContainer()->getParameter('kernel.root_dir') . DIRECTORY_SEPARATOR . 'OroRequirements.php';
        }

        $collection = new \OroRequirements();

        $this->renderTable($collection->getMandatoryRequirements(), 'Mandatory requirements', $output);
        $this->renderTable($collection->getPhpIniRequirements(), 'PHP settings', $output);
        $this->renderTable($collection->getOroRequirements(), 'Oro specific requirements', $output);
        $this->renderTable($collection->getRecommendations(), 'Optional recommendations', $output);

        if (count($collection->getFailedRequirements())) {
            throw new \RuntimeException('Some system requirements are not fulfilled. Please check output messages and fix them.');
        }

        $output->writeln('');

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function setupStep(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Setting up database.</info>');

        $dialog    = $this->getHelperSet()->get('dialog');
        $container = $this->getContainer();
        $options   = $input->getOptions();

        $input->setInteractive(false);

        $this
            ->runCommand('oro:entity-extend:clear', $output)
            ->runCommand('doctrine:schema:drop', $output, array('--force' => true, '--full-database' => true))
            ->runCommand('doctrine:schema:create', $output)
            ->runCommand('doctrine:fixtures:load', $output, array('--no-interaction' => true))
            ->runCommand('oro:entity-config:init', $output)
            ->runCommand('oro:entity-extend:init', $output)
            ->runCommand('oro:entity-extend:update-config', $output);

        $output->writeln('');
        $output->writeln('<info>Administration setup.</info>');

        $user = $container->get('oro_user.manager')->createUser();
        $role = $container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('OroUserBundle:Role')
            ->findOneBy(array('role' => 'ROLE_ADMINISTRATOR'));

        $passValidator = function ($value) {
            if (strlen(trim($value)) < 2) {
                throw new \Exception('The password must be at least 2 characters long');
            }

            return $value;
        };

        $user
            ->setUsername(isset($options['user-name'])
                ? $options['user-name']
                : $dialog->ask($output, '<question>Username:</question> ')
            )
            ->setEmail(isset($options['user-email'])
                ? $options['user-email']
                : $dialog->ask($output, '<question>Email:</question> ')
            )
            ->setFirstname(isset($options['user-firstname'])
                ? $options['user-firstname']
                : $dialog->ask($output, '<question>First name:</question> ')
            )
            ->setLastname(isset($options['user-lastname'])
                ? $options['user-lastname']
                : $dialog->ask($output, '<question>Last name:</question> ')
            )
            ->setPlainPassword(isset($options['user-password'])
                ? $options['user-password']
                : $dialog->askHiddenResponseAndValidate($output, '<question>Password:</question> ', $passValidator)
            )
            ->setEnabled(true)
            ->addRole($role);

        $container->get('oro_user.manager')->updateUser($user);

        $demo = isset($options['sample-data'])
            ? true
            : $dialog->askConfirmation($output, '<question>Load sample data (y/n)?</question> ', false);

        // load demo fixtures
        if ($demo) {
            $loader = new ContainerAwareLoader($container);

            foreach ($container->get('kernel')->getBundles() as $bundle) {
                if (is_dir($path = $bundle->getPath() . '/DataFixtures/Demo')) {
                    $loader->loadFromDirectory($path);
                }
            }

            $executor = new ORMExecutor($container->get('doctrine.orm.entity_manager'));

            $executor->execute($loader->getFixtures(), true);
        }

        $output->writeln('');

        return $this;
    }

    protected function finalStep(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Preparing application.</info>');

        $input->setInteractive(false);

        $this
            ->runCommand('doctrine:schema:update', $output, array('--force' => true, '--no-interaction' => true))
            ->runCommand('oro:search:create-index', $output)
            ->runCommand('oro:navigation:init', $output)
            ->runCommand('assets:install', $output)
            ->runCommand('assetic:dump', $output)
            ->runCommand('oro:assetic:dump', $output)
            ->runCommand('oro:translation:dump', $output)
            ->runCommand('oro:requirejs:build', $output);

        $params = $this->getContainer()->get('oro_installer.yaml_persister')->parse();

        $params['system']['installed'] = date('c');

        $this->getContainer()->get('oro_installer.yaml_persister')->dump($params);

        $output->writeln('');

        return $this;
    }

    /**
     * Render requirements table
     *
     * @param array  $collection
     * @param string $header
     */
    protected function renderTable(array $collection, $header, OutputInterface $output)
    {
        $table = $this->getHelperSet()->get('table');

        $table
            ->setHeaders(array('Check  ', $header))
            ->setRows(array());

        foreach ($collection as $requirement) {
            if ($requirement->isFulfilled()) {
                $table->addRow(array('OK', $requirement->getTestMessage()));
            } else {
                $table->addRow(array(
                    $requirement->isOptional() ? 'WARNING' : 'ERROR',
                    $requirement->getHelpText()
                ));
            }
        }

        $table->render($output);
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
