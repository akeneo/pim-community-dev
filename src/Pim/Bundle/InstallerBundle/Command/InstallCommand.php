<?php

namespace Pim\Bundle\InstallerBundle\Command;

use Symfony\Component\Console\Input\ArrayInput;

use Symfony\Component\Process\ProcessBuilder;

use Symfony\Component\Process\PhpExecutableFinder;

use Symfony\Component\Console\Input\InputOption;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\InstallerBundle\Command\InstallCommand as OroInstallCommand;

/**
 * Override OroInstaller command to add PIM custom rules
 *
 * @author    Nicolas <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallCommand extends OroInstallCommand
{
    const APP_NAME    = 'Akeneo PIM';

    const TASK_ALL    = 'all';
    const TASK_ASSETS = 'assets';
    const TASK_CHECK  = 'check';
    const TASK_DB     = 'db';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('pim:install')
            ->setDescription(static::APP_NAME .' Application Installer.')
            ->addOption(
                'task',
                null,
                InputOption::VALUE_REQUIRED,
                'Determines tasks called for installation (can be all, check, db or assets)',
                self::TASK_ALL
            );
    }

    protected function setupStep(InputInterface $input, OutputInterface $output)
    {
        $task = $input->getOption('task');

        if ($task === self::TASK_DB || $task === self::TASK_ALL) {
            $this->oroSetupStep($input, $output);

            $this
                ->runCommand('oro:search:create-index', $input, $output)
                ->runCommand(
                    'pim:search:reindex',
                    $input,
                    $output,
                    array('locale' => $this->getContainer()->getParameter('locale'))
                )
                ->runCommand('pim:versioning:refresh', $input, $output)
                // @TODO: Get pim_catalog_product_value table from ProductValue metadata
                ->runCommand(
                    'doctrine:query:sql',
                    $input,
                    $output,
                    array('sql' => 'ANALYZE TABLE pim_catalog_product_value')
                )
                ->runCommand(
                    'pim:completeness:calculate',
                    $input,
                    $output
                );
        }

        return $this;
    }

    /**
     * Override of parent class setupStep method
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     * @return \Pim\Bundle\InstallerBundle\Command\InstallCommand
     */
    private function oroSetupStep(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Setting up database.</info>');

        $dialog    = $this->getHelperSet()->get('dialog');
        $container = $this->getContainer();
        $options   = $input->getOptions();

        $input->setInteractive(false);

        $this
//         ->runCommand('oro:entity-extend:clear', $input, $output)
//         ->runCommand('doctrine:schema:drop', $input, $output, array('--force' => true, '--full-database' => true))
        ->runCommand('doctrine:schema:create', $input, $output)
        ->runCommand('oro:entity-config:init', $input, $output)
        ->runCommand('oro:entity-extend:init', $input, $output)
        ->runCommand(
            'oro:entity-extend:update-config',
            $input,
            $output,
            array('--process-isolation' => true)
        )
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

    /**
     * {@inheritdoc}
     */
    protected function checkStep(InputInterface $input, OutputInterface $output)
    {
        $task = $input->getOption('task');

        if ($task === self::TASK_CHECK || $task === self::TASK_ALL) {
            $output->writeln('<info>Akeneo PIM requirements check:</info>');
            if (!class_exists('PimRequirements')) {
                require_once $this->getContainer()->getParameter('kernel.root_dir')
                    . DIRECTORY_SEPARATOR
                    . 'PimRequirements.php';
            }

            $collection = new \PimRequirements($this->getDirectoriesToCheck());

            $this->renderTable($collection->getMandatoryRequirements(), 'Mandatory requirements', $output);
            $this->renderTable($collection->getPhpIniRequirements(), 'PHP settings', $output);
            $this->renderTable($collection->getOroRequirements(), 'Oro specific requirements', $output);
            $this->renderTable($collection->getPimRequirements(), 'Pim specific requirements', $output);
            $this->renderTable($collection->getRecommendations(), 'Optional recommendations', $output);

            if (count($collection->getFailedRequirements())) {
                throw new \RuntimeException(
                    'Some system requirements are not fulfilled. Please check output messages and fix them.'
                );
            }

            $output->writeln('');
        }

        return $this;
    }

    /**
     * Get list of directories to check for PimRequirements
     *
     * @return array
     */
    protected function getDirectoriesToCheck()
    {
        $directories = array();
        $directories[] = $this->getContainer()->getParameter('upload_dir');
        $directories[] = $this->getContainer()->getParameter('archive_dir');

        return $directories;
    }

    /**
     * {@inheritdoc}
     */
    protected function finalStep(InputInterface $input, OutputInterface $output)
    {
        $task = $input->getOption('task');
        if ($task === self::TASK_ASSETS || $task === self::TASK_ALL) {
            $this->oroFinalStep($input, $output);
        }

        return $this;
    }

    /**
     * Override parent class finalStep method
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return \Pim\Bundle\InstallerBundle\Command\InstallCommand
     */
    private function oroFinalStep(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Preparing application.</info>');

        $input->setInteractive(false);

        $this
            ->runCommand('fos:js-routing:dump', $input, $output, array('--target' => 'web/js/routes.js'))
            ->runCommand('oro:navigation:init', $input, $output)
            ->runCommand('assets:install', $input, $output)
            ->runCommand('assetic:dump', $input, $output)
            ->runCommand('oro:assetic:dump', $input, $output)
            ->runCommand('oro:translation:dump', $input, $output)
            ->runCommand('oro:localization:dump', $input, $output);

        $output->writeln('');

        return $this;
    }

    /**
     * Update installed flag in parameters.yml and clear it from DI container
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return InstallCommand
     */
    protected function updateInstalledFlag(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Updating installed flag</info>');

        $dumper = $this->getContainer()->get('oro_installer.yaml_persister');
        $params = $dumper->parse();
        $params['system']['installed'] = date('c');
        $dumper->dump($params);

        $this->runCommand('cache:clear', $input, $output);
        $output->writeln('');

        return $this;
    }
}
