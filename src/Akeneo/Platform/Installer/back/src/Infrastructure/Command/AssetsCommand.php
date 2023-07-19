<?php

namespace Akeneo\Platform\Installer\Infrastructure\Command;

use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvents;
use Akeneo\Tool\Component\Console\CommandExecutor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Assets dump command.
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssetsCommand extends Command
{
    protected static $defaultName = 'pim:installer:assets';

    /** @var CommandExecutor */
    protected $commandExecutor;

    /**
     * @param array<string> $defaultLocales
     */
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly array $defaultLocales,
        private readonly string $rootDir,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Install assets for Akeneo PIM')
            ->addOption('symlink', null, InputOption::VALUE_NONE, 'Install assets as symlinks')
            ->addOption('clean', null, InputOption::VALUE_NONE, 'Clean previous assets');
    }

    /**
     * {@inheritdoc}
     */
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
        $output->writeln('<info>Akeneo PIM assets</info>');

        $event = new GenericEvent();
        $event->setArguments([
            'clean' => $input->getOption('clean'),
            'symlink' => $input->getOption('symlink'),
        ]);

        $this->eventDispatcher->dispatch($event, InstallerEvents::PRE_ASSETS_DUMP);

        $webDir = $this->getWebDir();

        if (true === $input->getOption('clean')) {
            try {
                $this->cleanDirectories([$webDir.'bundles', $webDir.'css', $webDir.'js']);
            } catch (\Exception $e) {
                $output->writeln(sprintf('<error>Error during PIM installation. %s</error>', $e->getMessage()));
                $output->writeln('');

                return (int) $e->getCode();
            }
        }

        $this->commandExecutor
            ->runCommand('fos:js-routing:dump', ['--format' => 'json', '--target' => $webDir.'js/fos_js_routes.json'])
            ->runCommand('assets:install');

        $this->eventDispatcher->dispatch($event, InstallerEvents::POST_SYMFONY_ASSETS_DUMP);
        $this->commandExecutor
            ->runCommand('pim:installer:dump-require-paths');
        $this->commandExecutor->runCommand('oro:translation:dump', ['locale' => implode(', ', $this->defaultLocales)]);

        if (true === $input->getOption('symlink')) {
            $this->commandExecutor->runCommand('assets:install', ['--relative' => true, '--symlink' => true]);
        }

        $this->eventDispatcher->dispatch($event, InstallerEvents::POST_ASSETS_DUMP);

        return Command::SUCCESS;
    }

    /**
     * @return string
     */
    protected function getWebDir()
    {
        return $this->rootDir.'/../public/';
    }

    /**
     * Removes a list of directories and all its content.
     *
     * @param string[] $directories
     */
    protected function cleanDirectories($directories): void
    {
        foreach ($directories as $directory) {
            if ($this->filesystem->exists($directory)) {
                $this->filesystem->remove($directory);
            }
            $this->filesystem->mkdir($directory);
        }
    }
}
