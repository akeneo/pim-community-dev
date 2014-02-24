<?php

namespace Pim\Bundle\InstallerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

use Oro\Bundle\InstallerBundle\CommandExecutor;

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

    const LOAD_ALL    = 'all';
    const LOAD_ORO    = 'OroPlatform';

    const LOAD_FIXTURES_TIMEOUT = 0;

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
            )
            ->addOption(
                'fixtures',
                null,
                InputOption::VALUE_REQUIRED,
                'Determines fixtures to load (can be just OroPlatform or all)',
                self::LOAD_ALL
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->commandExecutor = new CommandExecutor(
            $input->hasOption('env') ? $input->getOption('env') : null,
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
        $output->writeln(sprintf('<info>%s requirements check:</info>', static::APP_NAME));

        if (!class_exists('OroRequirements')) {
            require_once $this->getContainer()->getParameter('kernel.root_dir')
                . DIRECTORY_SEPARATOR
                . 'OroRequirements.php';
        }

        $this->renderRequirements($input, $output, $this->getRequirements());

        $output->writeln('');

        return $this;
    }

    /**
     * Get requirements class
     *
     * @return \RequirementCollection
     */
    protected function getRequirements()
    {
        if (!class_exists('PimRequirements')) {
            require_once $this->getContainer()->getParameter('kernel.root_dir')
                . DIRECTORY_SEPARATOR . 'PimRequirements.php';
        }

        $directories = array();

        if ($this->getContainer()->getParameter('kernel.environment') !== 'behat') {
            $directories[] = $this->getContainer()->getParameter('upload_dir');
            $directories[] = $this->getContainer()->getParameter('archive_dir');
        }

        return new \PimRequirements($directories);
    }

    /**
     * Render Oro requirements
     *
     * @param InputInterface         $input
     * @param OutputInterface        $output
     * @param \RequirementCollection $collection
     *
     * @throws \RuntimeException
     */
    protected function renderRequirements(
        InputInterface $input,
        OutputInterface $output,
        \RequirementCollection $collection
    ) {
        $this->renderTable($collection->getMandatoryRequirements(), 'Mandatory requirements', $output);
        $this->renderTable($collection->getPhpIniRequirements(), 'PHP settings', $output);
        $this->renderTable($collection->getOroRequirements(), 'Oro specific requirements', $output);
        $this->renderTable($collection->getPimRequirements(), 'Pim specific requirements', $output);
        $this->renderTable($collection->getRecommendations(), 'Optional recommendations', $output);

        if (count($collection->getFailedRequirements())) {
            $this->renderTable($collection->getFailedRequirements(), 'Errors', $output);
            throw new \RuntimeException(
                'Some system requirements are not fulfilled. Please check output messages and fix them.'
            );
        }
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
        $output->writeln('<info>Prepare database schema</info>');
        $defaultParams = $this->getDefaultParams($input);

        $this->commandExecutor
            ->runCommand('doctrine:schema:drop', $defaultParams + array('--force' => true, '--full-database' => true))
            ->runCommand('doctrine:schema:create', $defaultParams)
            ->runCommand('oro:entity-config:init', $defaultParams)
            ->runCommand('oro:entity-extend:init', $defaultParams)
            ->runCommand(
                'oro:entity-extend:update-config',
                $defaultParams + array('--process-isolation' => true)
            )
            ->runCommand(
                'doctrine:schema:update',
                $defaultParams + array('--process-isolation' => true, '--force' => true, '--no-interaction' => true)
            );

        $this
            ->loadFixturesStep($input, $output)
            ->launchCommands($input, $output);

        return $this;
    }

    /**
     * Step where fixtures are loaded
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return InstallCommand
     */
    protected function loadFixturesStep(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('env') === 'behat') {
            $input->setOption('fixtures', self::LOAD_ORO);
        }
        $defaultParams = $this->getDefaultParams($input);

        $output->writeln('<info>Load fixtures.</info>');

        $params =
            $defaultParams
            + array(
                '--process-isolation' => true,
                '--no-interaction' => true,
                '--append' => true,
                '--process-timeout' => static::LOAD_FIXTURES_TIMEOUT
            )
            + $this->getFixturesList($input->getOption('fixtures'));

        $this->commandExecutor->runCommand('doctrine:fixtures:load', $params);

        $output->writeln('');

        return $this;
    }

    /**
     * Get fixtures to load list
     *
     * @param string $fixtureOpt
     *
     * @return array
     */
    protected function getFixturesList($fixtureOpt)
    {
        if ($fixtureOpt === self::LOAD_ORO) {
            $basePath = realpath($this->getContainer()->getParameter('kernel.root_dir') . DIRECTORY_SEPARATOR .'..');
            $phpFinder = new Finder();
            $directories = $phpFinder
                ->in($basePath)
                ->path('/Oro\/Bundle\/.*Bundle\/DataFixtures$/')
                ->directories();

            $oroFixtures = array();
            foreach ($directories as $directory) {
                $oroFixtures[] = $directory->getPathName();
            }

            return array('--fixtures' => $oroFixtures);
        }

        return array();
    }

    /**
     * Launchs all commands needed after fixtures loading
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return InstallCommand
     */
    protected function launchCommands(InputInterface $input, OutputInterface $output)
    {
        $defaultParams = $this->getDefaultParams($input);

        $this->commandExecutor
            ->runCommand('oro:search:create-index')
            ->runCommand(
                'pim:search:reindex',
                $defaultParams + array('locale' => $this->getContainer()->getParameter('locale'))
            )
            ->runCommand('pim:versioning:refresh', $defaultParams)
            ->runCommand('pim:completeness:calculate', $defaultParams);

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
        $output->writeln('<info>Preparing application.</info>');
        $defaultParams = $this->getDefaultParams($input);

        $this->commandExecutor
            ->runCommand('oro:navigation:init', $defaultParams)
            ->runCommand('fos:js-routing:dump', $defaultParams + array('--target' => 'web/js/routes.js'))
            ->runCommand('oro:localization:dump', $defaultParams)
            ->runCommand('assets:install', $defaultParams)
            ->runCommand('assetic:dump', $defaultParams)
            ->runCommand('oro:assetic:dump', $defaultParams)
            ->runCommand('oro:translation:dump', $defaultParams);

        $output->writeln('');

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

    /**
     * Run clear cache command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return CommandExecutor
     */
    protected function clearCache(InputInterface $input, OutputInterface $output)
    {
        $defaultParams = $this->getDefaultParams($input);

        return $this->commandExecutor
            ->runCommand('oro:entity-extend:clear', $defaultParams)
            ->runCommand('cache:clear', $defaultParams);
    }

    /**
     * Get default params
     *
     * @param InputInterface $input
     *
     * @return array
     */
    protected function getDefaultParams(InputInterface $input)
    {
        $defaultParams = array();
        if ($input->getOption('verbose')) {
            $defaultParams = array('--verbose' => true);
        }

        return $defaultParams;
    }

    /**
     * Render requirements table
     *
     * @param array           $collection
     * @param string          $header
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
}
