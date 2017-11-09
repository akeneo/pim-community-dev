<?php

namespace Pim\Bundle\InstallerBundle\Command;

use Pim\Bundle\InstallerBundle\CommandExecutor;
use Pim\Bundle\InstallerBundle\Event\InstallerEvents;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Assets dump command
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssetsCommand extends ContainerAwareCommand
{
    /** @var CommandExecutor */
    protected $commandExecutor;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:installer:assets')
            ->setDescription('Install assets for Akeneo PIM')
            ->addOption('symlink', null, InputOption::VALUE_NONE, 'Install assets as symlinks')
            ->addOption('clean', null, InputOption::VALUE_NONE, 'Clean previous assets');
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
        $output->writeln('<info>Akeneo PIM assets</info>');

        $this->getEventDispatcher()->dispatch(InstallerEvents::PRE_ASSETS_DUMP);

        $webDir = $this->getWebDir();

        if (true === $input->getOption('clean')) {
            try {
                $this->cleanDirectories([$webDir.'bundles', $webDir.'css', $webDir.'js']);
            } catch (\Exception $e) {
                $output->writeln(sprintf('<error>Error during PIM installation. %s</error>', $e->getMessage()));
                $output->writeln('');

                return $e->getCode();
            }
        }

        $this->commandExecutor
            ->runCommand('oro:navigation:init')
            ->runCommand('fos:js-routing:dump', ['--target' => $webDir.'js/routes.js'])
            ->runCommand('oro:requirejs:generate-config')
            ->runCommand('assets:install')
            ->runCommand('assetic:dump')
            ->runCommand('oro:assetic:dump');
        $defaultLocales = ['en', 'fr', 'nl', 'de', 'ru', 'ja', 'pt', 'it'];
        $this->commandExecutor->runCommand('oro:translation:dump', ['locale' => $defaultLocales]);

        if (true === $input->getOption('symlink')) {
            $this->commandExecutor->runCommand('assets:install', ['--relative' => true, '--symlink' => true]);
        }

        $this->getEventDispatcher()->dispatch(InstallerEvents::POST_ASSETS_DUMP);

        return $this;
    }

    /**
     * @return string
     */
    protected function getWebDir()
    {
        return $this->getContainer()->getParameter('kernel.root_dir').'/../web/';
    }

    /**
     * Removes a list of directories and all its content.
     *
     * @param string[] $directories
     */
    protected function cleanDirectories($directories)
    {
        $filesystem = $this->getContainer()->get('filesystem');

        foreach ($directories as $directory) {
            if ($filesystem->exists($directory)) {
                $filesystem->remove($directory);
            }
            $filesystem->mkdir($directory);
        }
    }

    /**
     * @return EventDispatcherInterface
     */
    protected function getEventDispatcher()
    {
        return $this->getContainer()->get('event_dispatcher');
    }
}
