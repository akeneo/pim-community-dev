<?php

namespace Pim\Bundle\InstallerBundle\Command;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Pim\Bundle\InstallerBundle\CommandExecutor;
use Pim\Bundle\InstallerBundle\FixtureLoader\FixtureJobLoader;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Database preparing command
 * - creates database
 * - updates schema
 * - loads fixtures
 * - launches other command for database calculations (completeness calculation)
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatabaseCommand extends ContainerAwareCommand
{
    /**
     * @staticvar string
     */
    const LOAD_ALL    = 'all';
    const LOAD_BASE   = 'base';

    /** @var CommandExecutor */
    protected $commandExecutor;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:installer:db')
            ->setDescription('Prepare database and load fixtures')
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
        $driver = $this->getStorageDriver();

        $output->writeln(sprintf('<info>Prepare database schema (driver: %s)</info>', $driver));

        // Needs to try if database already exists or not
        $connection = $this->getContainer()->get('doctrine')->getConnection();
        try {
            if (!$connection->isConnected()) {
                $connection->connect();
            }
            $this->commandExecutor->runCommand('doctrine:database:drop', ['--force' => true]);
        } catch (\PDOException $e) {
            $output->writeln(' <error>Database does not exist yet</error>');
        }

        $this->commandExecutor->runCommand('doctrine:database:create');

        if (AkeneoStorageUtilsExtension::DOCTRINE_MONGODB_ODM === $driver) {
            $this->commandExecutor
                ->runCommand('doctrine:mongodb:schema:drop')
                ->runCommand('doctrine:mongodb:schema:create');
        }

        // Needs to close connection if always open
        if ($connection->isConnected()) {
            $connection->close();
        }

        $this->commandExecutor
            ->runCommand('doctrine:schema:create')
            ->runCommand(
                'doctrine:schema:update',
                ['--force' => true, '--no-interaction' => true]
            );

        $this->createNotMappedTables($output);

        $this
            ->loadFixturesStep($input, $output)
            ->launchCommands($input, $output);

        return $this;
    }

    /**
     * Create tables not mapped to Doctrine entities
     *
     * @param OutputInterface $output
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function createNotMappedTables(OutputInterface $output)
    {
        $output->writeln('<info>Create session table</info>');

        $sessionTableSql = "CREATE TABLE pim_session (
                `sess_id` VARBINARY(128) NOT NULL PRIMARY KEY,
                `sess_data` BLOB NOT NULL,
                `sess_time` INTEGER UNSIGNED NOT NULL,
                `sess_lifetime` MEDIUMINT NOT NULL DEFAULT  '0'
            ) COLLATE utf8_bin, ENGINE = InnoDB";

        $db = $this->getContainer()->get('doctrine');

        $db->getConnection()->exec($sessionTableSql);
    }

    /**
     * Step where fixtures are loaded
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return DatabaseCommand
     */
    protected function loadFixturesStep(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('env') === 'behat') {
            $input->setOption('fixtures', self::LOAD_BASE);
        }

        $output->writeln(
            sprintf(
                '<info>Load jobs for fixtures. (data set: %s)</info>',
                $this->getContainer()->getParameter('installer_data')
            )
        );
        $this->getFixtureJobLoader()->load();

        $output->writeln(
            sprintf(
                '<info>Load fixtures. (data set: %s)</info>',
                $this->getContainer()->getParameter('installer_data')
            )
        );

        $params = [
                '--no-interaction' => true,
                '--append'         => true
            ]
            + $this->getFixturesList($input->getOption('fixtures'));

        $this->commandExecutor->runCommand('doctrine:fixtures:load', $params);

        if (AkeneoStorageUtilsExtension::DOCTRINE_MONGODB_ODM === $this->getStorageDriver()) {
            $this->commandExecutor->runCommand('doctrine:mongodb:fixtures:load', ['--append' => true]);
        }

        $output->writeln('');

        $output->writeln('<info>Delete jobs for fixtures.</info>');
        $this->getFixtureJobLoader()->deleteJobs();

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
        if ($fixtureOpt === self::LOAD_BASE) {
            $fixtures = $this->getOroFixturesList();
            $fixtures[] = realpath(__DIR__ . '/../DataFixtures/ORM/Base');

            return ['--fixtures' => $fixtures];
        }

        return [];
    }

    /**
     * Launchs all commands needed after fixtures loading
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return DatabaseCommand
     */
    protected function launchCommands(InputInterface $input, OutputInterface $output)
    {
        $this->commandExecutor
            ->runCommand('pim:versioning:refresh')
            ->runCommand('pim:completeness:calculate');

        return $this;
    }

    /**
     * Get the storage driver
     *
     * @return string
     */
    protected function getStorageDriver()
    {
        return $this->getContainer()->getParameter('pim_catalog_product_storage_driver');
    }

    /**
     * @return FixtureJobLoader
     */
    protected function getFixtureJobLoader()
    {
        return $this->getContainer()->get('pim_installer.fixture_loader.job_loader');
    }

    /**
     * Get the Oro fixtures list
     *
     * @return array
     */
    protected function getOroFixturesList()
    {
        $bundles = $this->getContainer()->getParameter('kernel.bundles');

        $basePath = realpath($this->getContainer()->getParameter('kernel.root_dir') . DIRECTORY_SEPARATOR .'..');
        $finder = new Finder();

        foreach ($bundles as $bundleName => $bundleNamespace) {
            if (strpos($bundleNamespace, 'Oro\\') === 0) {
                $bundle = $this->getContainer()->get('kernel')->getBundle($bundleName);
                $finder->in($bundle->getPath());
            }
        }
        // Oro User Bundle overriden by Pim User Bundle, but we still need the data fixtures inside OroUserBundle
        $finder->in($basePath."/vendor/oro/platform/src/Oro/Bundle/UserBundle");
        $directories = $finder
            ->path('/^DataFixtures$/')
            ->directories();

        $oroFixtures = [];
        foreach ($directories as $directory) {
            $oroFixtures[] = $directory->getPathName();
        }

        return $oroFixtures;
    }
}
