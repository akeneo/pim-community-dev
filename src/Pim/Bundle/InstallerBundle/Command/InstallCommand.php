<?php

namespace Pim\Bundle\InstallerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Pim\Bundle\InstallerBundle\CommandExecutor;

/**
 * Override OroInstaller command to add PIM custom rules
 *
 * @author    Nicolas <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallCommand extends ContainerAwareCommand
{
    /**
     * @staticvar string
     */
    const APP_NAME = 'Akeneo PIM';

    const TASK_ALL    = 'all';
    const TASK_ASSETS = 'assets';
    const TASK_CHECK  = 'check';
    const TASK_DB     = 'db';

    /**
     * @var CommandExecutor $commandExecutor
     */
    protected $commandExecutor;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:install')
            ->setDescription(sprintf('%s Application Installer.', static::APP_NAME))
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force installation')
            ->addOption(
                'task',
                null,
                InputOption::VALUE_REQUIRED,
                'Determines tasks called for installation (can be all, check, db or assets)',
                self::TASK_ALL
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->commandExecutor = new CommandExecutor(
            $input,
            $output,
            $this->getApplication()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $forceInstall = $input->getOption('force');
        // if there is application is not installed or no --force option
        if ($this->getContainer()->hasParameter('installed') && $this->getContainer()->getParameter('installed')
            && !$forceInstall
        ) {
            throw new \RuntimeException('Oro Application already installed.');
        } elseif ($forceInstall) {
            // if --force option we have to clear cache and set installed to false
            $this->updateInstalledFlag($input, $output, false);
        }

        $output->writeln(sprintf('<info>Installing %s Application.</info>', static::APP_NAME));
        $output->writeln('');

        switch ($input->getOption('task')) {
            case self::TASK_CHECK:
                $this->checkStep($input, $output);
                break;
            case self::TASK_DB:
                $this->databaseStep($input, $output);
                break;
            case self::TASK_ASSETS:
                $this->assetsStep($input, $output);
                break;
            default:
                $this
                    ->checkStep($input, $output)
                    ->databaseStep($input, $output)
                    ->assetsStep($input, $output);
                break;
        }

        $this->updateInstalledFlag($input, $output, date('c'));

        $output->writeln('');
        $output->writeln(sprintf('<info>%s Application has been successfully installed.</info>', static::APP_NAME));
    }

    /**
     * Step where configuration is checked
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \RuntimeException
     *
     * @return InstallCommand
     */
    protected function checkStep(InputInterface $input, OutputInterface $output)
    {
        $this->commandExecutor->runCommand('pim:installer:check-requirements');

        return $this;
    }

    /**
     * Step where the database is built, the fixtures loaded and some command scripts launched
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return InstallCommand
     */
    protected function databaseStep(InputInterface $input, OutputInterface $output)
    {
        $this->commandExecutor->runCommand('pim:installer:db');

        return $this;
    }

    /**
     * Load only assets
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return InstallCommand
     */
    protected function assetsStep(InputInterface $input, OutputInterface $output)
    {
        $this->commandExecutor->runCommand('pim:installer:assets');

        return $this;
    }

    /**
     * Update installed flag
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param boolean         $installed
     *
     * @return InstallCommand
     */
    protected function updateInstalledFlag(InputInterface $input, OutputInterface $output, $installed)
    {
        $output->writeln('<info>Updating installed flag.</info>');

        $dumper = $this->getContainer()->get('oro_installer.yaml_persister');
        $params = $dumper->parse();
        $params['system']['installed'] = $installed;
        $dumper->dump($params);
    }
}
