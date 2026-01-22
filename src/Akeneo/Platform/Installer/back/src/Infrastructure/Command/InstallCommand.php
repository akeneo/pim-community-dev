<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Infrastructure\Command;

use Akeneo\Platform\Installer\Infrastructure\InstallStatusManager\InstallStatusManager;
use Akeneo\Tool\Component\Console\CommandExecutor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Installer command to add PIM custom rules.
 *
 * @author    Nicolas <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallCommand extends Command
{
    protected static $defaultName = 'pim:install';
    protected static $defaultDescription = 'Akeneo PIM Application Installer.';

    private CommandExecutor $commandExecutor;

    public function __construct(private InstallStatusManager $installStatusManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force installation')
            ->addOption('symlink', null, InputOption::VALUE_NONE, 'Install assets as symlinks')
            ->addOption('clean', null, InputOption::VALUE_NONE, 'Clean previous install')
            ->addOption(
                'doNotDropDatabase',
                null,
                InputOption::VALUE_NONE,
                'Try to use an existing database if it already exists. Beware, the database data will still be deleted',
            );
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->commandExecutor = new CommandExecutor(
            $input,
            $output,
            $this->getApplication(),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
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
                ->databaseStep(['--doNotDropDatabase' => $input->getOption('doNotDropDatabase')])
                ->assetsStep($input);
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Error during PIM installation. %s</error>', $e->getMessage()));
            $output->writeln('');

            return (int) $e->getCode();
        }

        $output->writeln('');
        $output->writeln('<info>Akeneo PIM Application has been successfully installed.</info>');

        return Command::SUCCESS;
    }

    /**
     * Step where configuration is checked.
     *
     * @throws \RuntimeException
     */
    protected function checkStep(): self
    {
        $this->commandExecutor->runCommand('pim:installer:check-requirements');

        return $this;
    }

    /**
     * Step where the database is built, the fixtures loaded and some command scripts launched.
     *
     * @param array<string, mixed> $arguments
     */
    protected function databaseStep(array $arguments = []): self
    {
        $this->commandExecutor->runCommand('pim:installer:db', $arguments);

        return $this;
    }

    /**
     * Load only assets.
     */
    protected function assetsStep(InputInterface $input): self
    {
        $options = false === $input->getOption('symlink') ? [] : ['--symlink' => true];
        $options = false === $input->getOption('clean') ? $options : array_merge($options, ['--clean' => true]);

        $this->commandExecutor->runCommand('pim:installer:assets', $options);

        return $this;
    }

    protected function isPimInstalled(OutputInterface $output): bool
    {
        $output->writeln('<info>Check PIM installation</info>');

        return $this->installStatusManager->isPimInstalled();
    }
}
