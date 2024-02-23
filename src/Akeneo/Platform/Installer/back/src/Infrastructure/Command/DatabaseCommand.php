<?php

namespace Akeneo\Platform\Installer\Infrastructure\Command;

use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvent;
use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvents;
use Akeneo\Platform\Installer\Infrastructure\FixtureLoader\FixtureJobLoader;
use Akeneo\Platform\Installer\Infrastructure\Persistence\Sql\GetInstallDatetime;
use Akeneo\Platform\Installer\Infrastructure\Persistence\Sql\InstallData;
use Akeneo\Tool\Bundle\ElasticsearchBundle\ClientRegistry;
use Akeneo\Tool\Component\Console\CommandExecutor;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\Process;

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
class DatabaseCommand extends Command
{
    protected static $defaultName = 'pim:installer:db';
    protected static $defaultDescription = 'Prepare database and load fixtures';

    protected ?CommandExecutor $commandExecutor;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ClientRegistry $clientRegistry,
        protected readonly Connection $connection,
        private readonly FixtureJobLoader $fixtureJobLoader,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly InstallData $installTimeQuery,
        private readonly LoggerInterface $logger,
        private readonly GetInstallDatetime $getInstallDatetime,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addOption(
                'fixtures-to-skip',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Determines fixtures to not load',
                []
            )
            ->addOption(
                'withoutIndexes',
                null,
                InputOption::VALUE_OPTIONAL,
                'Should the command setup the elastic search indexes',
                false
            )
            ->addOption(
                'withoutFixtures',
                null,
                InputOption::VALUE_OPTIONAL,
                'Should the command install any fixtures',
                false
            )
            ->addOption(
                'catalog',
                null,
                InputOption::VALUE_OPTIONAL,
                'Directory of the fixtures to install',
                'src/Akeneo/Platform/Installer/back/src/Infrastructure/Symfony/Resources/fixtures/minimal'
            )
        ;
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
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->info('Prepare database schema');

        try {
            $this->commandExecutor
                ->runCommand(
                    'doctrine:schema:update',
                    ['--force' => true, '--no-interaction' => true, '--complete' => true]
                );
        } catch (\Exception $e) {
            $this->logger->critical('Trying to install PIM on an existing database is impossible.');
            $this->logger->critical($e->getMessage());

            return Command::FAILURE;
        }

        if (false === $input->getOption('withoutIndexes')) {
            $this->resetElasticsearchIndex($output);
        }

        $entityManager = $this->entityManager;
        $entityManager->clear();

        $this->eventDispatcher->dispatch(
            new InstallerEvent($this->commandExecutor, null, [
                'catalog' => $input->getOption('catalog'),
            ]),
            InstallerEvents::POST_DB_CREATE
        );

        $this->setLatestKnownMigration($input);

        if (false === $input->getOption('withoutFixtures')) {
            $this->eventDispatcher->dispatch(
                new InstallerEvent($this->commandExecutor, null, [
                    'catalog' => $input->getOption('catalog'),
                ]),
                InstallerEvents::PRE_LOAD_FIXTURES
            );

            $this->loadFixturesStep($input, $output);

            $this->eventDispatcher->dispatch(
                new InstallerEvent($this->commandExecutor, null, [
                    'catalog' => $input->getOption('catalog'),
                ]),
                InstallerEvents::POST_LOAD_FIXTURES
            );
        }

        if (null === ($this->getInstallDatetime)()) {
            $this->installTimeQuery->withDatetime(new \DateTimeImmutable());
        }

        return Command::SUCCESS;
    }

    /**
     * TODO: TIP-613: This should be done with a command.
     * TODO: TIP-613: This command should be able to drop/create indexes, and/or re-index products.
     */
    protected function resetElasticsearchIndex(OutputInterface $output)
    {
        $this->logger->info('Reset elasticsearch indexes');

        $clients = $this->clientRegistry->getClients();

        foreach ($clients as $client) {
            $client->resetIndex();
        }
    }

    protected function loadFixturesStep(InputInterface $input, OutputInterface $output): DatabaseCommand
    {
        $catalog = $input->getOption('catalog');

        $this->logger->info(sprintf('Load jobs for fixtures. (data set: %s)', $catalog));
        $this->fixtureJobLoader->loadJobInstances($input->getOption('catalog'));

        $jobInstances = $this->fixtureJobLoader->getLoadedJobInstances();
        foreach ($jobInstances as $jobInstance) {
            if (in_array($jobInstance->getCode(), $input->getOption('fixtures-to-skip'))) {
                continue;
            }

            $params = [
                'code' => $jobInstance->getCode(),
                '--no-debug' => true,
                '--no-log' => true,
                '-v' => true,
            ];

            $this->eventDispatcher->dispatch(
                new InstallerEvent($this->commandExecutor, $jobInstance->getCode(), [
                    'catalog' => $catalog,
                ]),
                InstallerEvents::PRE_LOAD_FIXTURE
            );

            $this->logger->info(
                sprintf('Please wait, the "%s" are processing...', $jobInstance->getCode())
            );
            $this->commandExecutor->runCommand('akeneo:batch:job', $params);
            $this->eventDispatcher->dispatch(
                new InstallerEvent($this->commandExecutor, $jobInstance->getCode(), [
                    'job_name' => $jobInstance->getJobName(),
                    'catalog' => $catalog,
                ]),
                InstallerEvents::POST_LOAD_FIXTURE
            );
        }

        $this->logger->info('Delete jobs for fixtures');
        $this->fixtureJobLoader->deleteJobInstances();

        return $this;
    }

    private function setLatestKnownMigration(InputInterface $input): void
    {
        $latestMigration = $this->getLatestMigration($input);

        $this->commandExecutor->runCommand('doctrine:migrations:sync-metadata-storage', ['-q' => true]);

        $this->commandExecutor->runCommand(
            'doctrine:migrations:version',
            ['version' => $latestMigration, '--add' => true, '--all' => true, '-q' => true]
        );
    }

    private function getLatestMigration(InputInterface $input): string
    {
        $params = ['php', 'bin/console', 'doctrine:migrations:latest'];

        $params[] = '--no-debug';

        if ($input->hasOption('env')) {
            $params[] = '--env';
            $params[] = $input->getOption('env');
        }

        if ($input->hasOption('verbose') && $input->getOption('verbose') === true) {
            $params[] = '--verbose';
        }

        $latestMigrationProcess = new Process($params);
        $latestMigrationProcess->run();

        if ($latestMigrationProcess->getExitCode() !== 0) {
            throw new \RuntimeException(
                "Impossible to get the latest migration {$latestMigrationProcess->getErrorOutput()}"
            );
        }

        return $latestMigrationProcess->getOutput();
    }
}
