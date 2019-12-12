<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\InstallerBundle\Command;

use Akeneo\Platform\Bundle\InstallerBundle\CommandExecutor;
use Akeneo\Platform\Bundle\InstallerBundle\InstallStatusManager\InstallStatusManager;
use Symfony\Component\Console\Command\Command;
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
class InstallCommand extends Command
{
    protected static $defaultName = 'pim:install';

    /** @var CommandExecutor */
    private $commandExecutor;

    /** @var InstallStatusManager */
    private $installStatusManager;

    public function __construct(InstallStatusManager $installStatusManager)
    {
        parent::__construct();
        $this->installStatusManager = $installStatusManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Akeneo PIM Application Installer.')
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

        if ($this->isPimInstalled($output) && false === $forceInstall) {
            throw new \RuntimeException('Akeneo PIM is already installed.');
        }

        $output->writeln('<info>Installing Akeneo PIM Application.</info>');
        $output->writeln('');

        try {
            $this
                ->checkStep()
                ->databaseStep()
                ->assetsStep($input);
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Error during PIM installation. %s</error>', $e->getMessage()));
            $output->writeln('');

            return $e->getCode();
        }

        $output->writeln('');
        $output->writeln('<info>Akeneo PIM Application has been successfully installed.</info>');

        return 0;
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
     * @param OutputInterface $output
     *
     * @return boolean
     */
    protected function isPimInstalled(OutputInterface $output) : bool
    {
        $output->writeln('<info>Check PIM installation</info>');

        return $this->installStatusManager->isPimInstalled();
    }
}
