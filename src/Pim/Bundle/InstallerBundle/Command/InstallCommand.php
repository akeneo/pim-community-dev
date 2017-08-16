<?php

namespace Pim\Bundle\InstallerBundle\Command;

use Pim\Bundle\InstallerBundle\CommandExecutor;
use Pim\Bundle\InstallerBundle\PimDirectoriesRegistry;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

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

        try {
            foreach ($this->getDirectoriesContainer()->getDirectories() as $directory) {
                $this->cleanDirectory($directory);
            }

            $this
                ->checkStep($input, $output)
                ->databaseStep($input, $output)
                ->assetsStep($input, $output);
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>Error during PIM installation. %s</error>', $e->getMessage()));
            $output->writeln('');

            return $e->getCode();
        }

        $this->updateInstalledFlag($input, $output, date('c'));

        $output->writeln('');
        $output->writeln(sprintf('<info>%s Application has been successfully installed.</info>', static::APP_NAME));

        return 0;
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
        $options = false === $input->getOption('symlink') ? [] : ['--symlink' => true];
        $options = false === $input->getOption('clean') ? $options : array_merge($options, ['--clean' => true]);

        $this->commandExecutor->runCommand('pim:installer:assets', $options);

        return $this;
    }

    /**
     * Update installed flag
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param bool            $installed
     *
     * @return InstallCommand
     */
    protected function updateInstalledFlag(InputInterface $input, OutputInterface $output, $installed)
    {
        $output->writeln('<info>Updating installed flag.</info>');

        $dumper = $this->getContainer()->get('pim_installer.yaml_persister');
        $params = $dumper->parse();
        $params['system']['installed'] = $installed;
        $dumper->dump($params);

        return $this;
    }

    /**
     * Remove directory and all subcontent
     *
     * @param string $folder
     */
    protected function cleanDirectory($folder)
    {
        $filesystem = new Filesystem();
        if ($filesystem->exists($folder)) {
            $filesystem->remove($folder);
        }
        $filesystem->mkdir($folder);
    }

    /**
     * @return PimDirectoriesRegistry
     */
    protected function getDirectoriesContainer()
    {
        return $this->getContainer()->get('pim_installer.directories_registry');
    }
}
