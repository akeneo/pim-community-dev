<?php

namespace Oro\Bundle\InstallerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\ProcessBuilder;

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
            ->addOption(
                'sample-data',
                null,
                InputOption::VALUE_OPTIONAL,
                'Determines whether sample data need to be loaded or not'
            );
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
            require_once $this->getContainer()->getParameter('kernel.root_dir')
                . DIRECTORY_SEPARATOR
                . 'OroRequirements.php';
        }

        $collection = new \OroRequirements();

        $this->renderTable($collection->getMandatoryRequirements(), 'Mandatory requirements', $output);
        $this->renderTable($collection->getPhpIniRequirements(), 'PHP settings', $output);
        $this->renderTable($collection->getOroRequirements(), 'Oro specific requirements', $output);
        $this->renderTable($collection->getRecommendations(), 'Optional recommendations', $output);

        if (count($collection->getFailedRequirements())) {
            throw new \RuntimeException(
                'Some system requirements are not fulfilled. Please check output messages and fix them.'
            );
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
            ->runCommand('oro:entity-extend:clear', $input, $output)
            ->runCommand('doctrine:schema:drop', $input, $output, array('--force' => true, '--full-database' => true))
            ->runCommand('doctrine:schema:create', $input, $output)
            ->runCommand('oro:entity-config:init', $input, $output)
            ->runCommand('oro:entity-extend:init', $input, $output)
            ->runCommand('oro:entity-extend:update-config', $input, $output)
            ->runCommand(
                'doctrine:schema:update',
                $input,
                $output,
                array('--process-isolation' => true, '--force' => true, '--no-interaction' => true)
            )
            ->runCommand(
                'doctrine:fixtures:load',
                $input,
                $output,
                array('--process-isolation' => true, '--no-interaction' => true, '--append' => true)
            );

        $output->writeln('');
        $output->writeln('<info>Administration setup.</info>');

        $user = $container->get('oro_user.manager')->createUser();
        $role = $container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('OroUserBundle:Role')
            ->findOneBy(array('role' => 'ROLE_ADMINISTRATOR'));

        $businessUnit = $container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('OroOrganizationBundle:BusinessUnit')
            ->findOneBy(array('name' => 'Main'));

        $passValidator = function ($value) {
            if (strlen(trim($value)) < 2) {
                throw new \Exception('The password must be at least 2 characters long');
            }

            return $value;
        };

        $userName = isset($options['user-name'])
            ? $options['user-name']
            : $dialog->ask($output, '<question>Username:</question> ');
        $userEmail = isset($options['user-email'])
            ? $options['user-email']
            : $dialog->ask($output, '<question>Email:</question> ');
        $userFirstName = isset($options['user-firstname'])
            ? $options['user-firstname']
            : $dialog->ask($output, '<question>First name:</question> ');
        $userLastName = isset($options['user-lastname'])
            ? $options['user-lastname']
            : $dialog->ask($output, '<question>Last name:</question> ');
        $userPassword = isset($options['user-password'])
            ? $options['user-password']
            : $dialog->askHiddenResponseAndValidate($output, '<question>Password:</question> ', $passValidator);
        $user
            ->setUsername($userName)
            ->setEmail($userEmail)
            ->setFirstName($userFirstName)
            ->setLastName($userLastName)
            ->setPlainPassword($userPassword)
            ->setEnabled(true)
            ->addRole($role)
            ->setOwner($businessUnit)
            ->addBusinessUnit($businessUnit);

        $container->get('oro_user.manager')->updateUser($user);

        $demo = isset($options['sample-data'])
            ? strtolower($options['sample-data']) == 'y'
            : $dialog->askConfirmation($output, '<question>Load sample data (y/n)?</question> ', false);

        // load demo fixtures
        if ($demo) {
            $this->runCommand(
                'oro:demo:fixtures:load',
                $input,
                $output,
                array('--process-isolation' => true, '--process-timeout' => 300)
            );
        }

        $output->writeln('');

        return $this;
    }

    protected function finalStep(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Preparing application.</info>');

        $input->setInteractive(false);

        $this
            ->runCommand('oro:search:create-index', $input, $output)
            ->runCommand('oro:navigation:init', $input, $output)
            ->runCommand('fos:js-routing:dump', $input, $output, array('--target' => 'web/js/routes.js'))
            ->runCommand('oro:localization:dump', $input, $output)
            ->runCommand('assets:install', $input, $output)
            ->runCommand('assetic:dump', $input, $output)
            ->runCommand('oro:assetic:dump', $input, $output)
            ->runCommand('oro:translation:dump', $input, $output)
            ->runCommand('oro:requirejs:build', $input, $output);

        // update installed flag in parameters.yml
        $dumper = $this->getContainer()->get('oro_installer.yaml_persister');
        $params = $dumper->parse();
        $params['system']['installed'] = date('c');
        $dumper->dump($params);

        // clear the cache set installed flag in DI container
        $this->runCommand('cache:clear', $input, $output);
 
        $output->writeln('');
        return $this;
    }

    /**
     * Render requirements table
     *
     * @param array  $collection
     * @param string $header
     * @param OutputInterface $output
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
                $table->addRow(
                    array(
                        $requirement->isOptional() ? 'WARNING' : 'ERROR',
                        $requirement->getHelpText()
                    )
                );
            }
        }

        $table->render($output);
    }

    /**
     * Launches a command.
     * If '--process-isolation' parameter is specified the command will be launched as a separate process.
     * In this case you can parameter '--process-timeout' to set the process timeout
     * in seconds. Default timeout is 60 seconds.
     *
     * @param string          $command
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param array           $params
     * @return InstallCommand
     */
    private function runCommand($command, InputInterface $input, OutputInterface $output, $params = array())
    {
        $params = array_merge(
            array(
                'command'    => $command,
                '--no-debug' => true,
            ),
            $params
        );
        if ($input->hasOption('env') && $input->getOption('env') !== 'dev') {
            $params['--env'] = $input->getOption('env');
        }

        if (array_key_exists('--process-isolation', $params)) {
            unset($params['--process-isolation']);
            $phpFinder = new PhpExecutableFinder();
            $php = $phpFinder->find();
            $pb = new ProcessBuilder();
            $pb
                ->add($php)
                ->add($_SERVER['argv'][0]);

            if (array_key_exists('--process-timeout', $params)) {
                $pb->setTimeout($params['--process-timeout']);
                unset($params['--process-timeout']);
            }

            foreach ($params as $param => $val) {
                if ($param && '-' === $param[0]) {
                    if ($val === true) {
                        $pb->add($param);
                    } else {
                        $pb->add($param . '=' . $val);
                    }
                } else {
                    $pb->add($val);
                }
            }

            $process = $pb
                ->inheritEnvironmentVariables(true)
                ->getProcess();

            $process->run(
                function ($type, $data) use ($output) {
                    $output->write($data);
                }
            );
            $ret = $process->getExitCode();
        } else {
            $this->getApplication()->setAutoExit(false);
            $ret = $this->getApplication()->run(new ArrayInput($params), $output);
        }

        if (0 !== $ret) {
            $output->writeln(sprintf('<error>The command terminated with an error status (%s)</error>', $ret));
        }

        return $this;
    }
}
