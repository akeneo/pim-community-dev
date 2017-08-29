<?php

namespace Pim\Bundle\InstallerBundle\Command;

use Pim\Bundle\InstallerBundle\CommandExecutor;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Installer command to add PIM custom rules
 *
 * @author    Nicolas <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallCommand extends ContainerAwareCommand
{
    /** @staticvar string */
    const APP_NAME = 'Akeneo PIM';

    /** @var CommandExecutor */
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
            ->addOption('symlink', null, InputOption::VALUE_NONE, 'Install assets as symlinks')
            ->addOption('clean', null, InputOption::VALUE_NONE, 'Clean previous install');
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

        // if the application is already installed or no --force option
        if ($this->isPimInstalled(($output)
            && !$forceInstall) {
            throw new \RuntimeException('Akeneo PIM is already installed.');
        }

        $output->writeln(sprintf('<info>Installing %s Application.</info>', static::APP_NAME));
        $output->writeln('');

        try {
            $this
                ->prepareRequiredDirectoriesStep()
                ->checkStep()
                ->databaseStep()
                ->assetsStep($input);
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Error during PIM installation. %s</error>', $e->getMessage()));
            $output->writeln('');

            return $e->getCode();
        }

        $output->writeln('');
        $output->writeln(sprintf('<info>%s Application has been successfully installed.</info>', static::APP_NAME));

        return 0;
    }

    /**
     * Step where required directories are created.
     *
     * @throws \RuntimeException
     *
     * @return InstallCommand
     */
    protected function prepareRequiredDirectoriesStep(): InstallCommand
    {
        $this->commandExecutor->runCommand('pim:installer:prepare-required-directories');

        return $this;
    }

    /**
     * Step where configuration is checked
     *
     * @throws \RuntimeException
     *
     * @return InstallCommand
     */
    protected function checkStep()
    {
        $this->commandExecutor->runCommand('pim:installer:check-requirements');

        return $this;
    }

    /**
     * Step where the database is built, the fixtures loaded and some command scripts launched
     *
     * @return InstallCommand
     */
    protected function databaseStep()
    {
        $this->commandExecutor->runCommand('pim:installer:db');

        return $this;
    }

    /**
     * Load only assets
     *
     * @param InputInterface $input
     *
     * @return InstallCommand
     */
    protected function assetsStep(InputInterface $input)
    {
        $options = false === $input->getOption('symlink') ? [] : ['--symlink' => true];
        $options = false === $input->getOption('clean') ? $options : array_merge($options, ['--clean' => true]);

        $this->commandExecutor->runCommand('pim:installer:assets', $options);

        return $this;
    }

    /**
     *
     * @param OutputInterface $output
     *
     * @return boolean isInstalled
     */
    protected function isPimInstalled(OutputInterface $output) : bool
    {
        $installStatus = $this->getContainer()->get('pim_installer.install_status_manager');

        $output->writeln('<info>Check installed flag</info>');

        return $installStatus->isInstalled();
    }
}
