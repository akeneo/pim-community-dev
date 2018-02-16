<?php

namespace Pim\Bundle\InstallerBundle\Command;

use Doctrine\DBAL\Exception\ConnectionException;
use Pim\Bundle\InstallerBundle\CommandExecutor;
use Pim\Bundle\InstallerBundle\Event\InstallerEvents;
use Pim\Bundle\InstallerBundle\FixtureLoader\FixtureJobLoader;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

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
    const LOAD_ALL = 'all';
    const LOAD_BASE = 'base';

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
        $output->writeln('<info>Prepare database schema</info>');

        // Needs to try if database already exists or not
        $connection = $this->getContainer()->get('doctrine')->getConnection();
        try {
            if (!$connection->isConnected()) {
                $connection->connect();
            }
            $this->commandExecutor->runCommand('doctrine:database:drop', ['--force' => true]);
        } catch (ConnectionException $e) {
            $output->writeln('<error>Database does not exist yet</error>');
        }

        $this->commandExecutor->runCommand('doctrine:database:create');

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

        $this->resetElasticsearchIndex($output);

        $entityManager = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $entityManager->clear();

        $this->getEventDispatcher()->dispatch(InstallerEvents::POST_DB_CREATE);

        // TODO: Should be in an event subscriber
        $this->createNotMappedTables($output);

        $this->getEventDispatcher()->dispatch(InstallerEvents::PRE_LOAD_FIXTURES);
        $this->loadFixturesStep($input, $output);
        $this->getEventDispatcher()->dispatch(InstallerEvents::POST_LOAD_FIXTURES);

        // TODO: Should be in an event subscriber
        $this->launchCommands();

        return $this;
    }

    /**
     * TODO: TIP-613: This should be done with a command.
     * TODO: TIP-613: This command should be able to drop/create indexes, and/or re-index products.
     *
     * @param OutputInterface $output
     */
    protected function resetElasticsearchIndex(OutputInterface $output)
    {
        $output->writeln('<info>Reset elasticsearch indexes</info>');

        $clientRegistry = $this->getContainer()->get('akeneo_elasticsearch.registry.clients');
        $clients = $clientRegistry->getClients();

        foreach ($clients as $client) {
            $client->resetIndex();
        }
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
        $this->getFixtureJobLoader()->loadJobInstances();

        $jobInstances = $this->getFixtureJobLoader()->getLoadedJobInstances();
        foreach ($jobInstances as $jobInstance) {
            $params = [
                'code'       => $jobInstance->getCode(),
                '--no-debug' => true,
                '--no-log'   => true,
                '-v'         => true
            ];

            $this->getEventDispatcher()->dispatch(
                InstallerEvents::PRE_LOAD_FIXTURE,
                new GenericEvent($jobInstance->getCode())
            );
            if ($input->getOption('verbose')) {
                $output->writeln(
                    sprintf(
                        'Please wait, the <comment>%s</comment> are processing...',
                        $jobInstance->getCode()
                    )
                );
            }
            $this->commandExecutor->runCommand('akeneo:batch:job', $params);
            $this->getEventDispatcher()->dispatch(
                InstallerEvents::POST_LOAD_FIXTURE,
                new GenericEvent($jobInstance->getCode())
            );
        }
        $output->writeln('');

        $output->writeln('<info>Delete jobs for fixtures.</info>');
        $this->getFixtureJobLoader()->deleteJobInstances();

        return $this;
    }

    /**
     * Launches all commands needed after fixtures loading
     *
     * @return DatabaseCommand
     */
    protected function launchCommands()
    {
        $this->commandExecutor->runCommand('pim:versioning:refresh');

        return $this;
    }

    /**
     * @return FixtureJobLoader
     */
    protected function getFixtureJobLoader()
    {
        return $this->getContainer()->get('pim_installer.fixture_loader.job_loader');
    }

    /**
     * @return EventDispatcherInterface
     */
    protected function getEventDispatcher()
    {
        return $this->getContainer()->get('event_dispatcher');
    }
}
