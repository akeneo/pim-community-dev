#!/usr/bin/env php
<?php

use Akeneo\Platform\Bundle\InstallerBundle\Command\DatabaseCommand;use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Akeneo\Platform\Bundle\InstallerBundle\FixtureLoader\FixtureJobLoader;use Akeneo\Platform\Bundle\InstallerBundle\Persistence\Sql\InstallData;use Akeneo\Tool\Bundle\ElasticsearchBundle\ClientRegistry;use Akeneo\Tool\Component\Console\CommandExecutor;use Doctrine\DBAL\Connection;use Doctrine\ORM\EntityManagerInterface;use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\Console\Output\NullOutput;use Symfony\Component\EventDispatcher\EventDispatcherInterface;use Symfony\Component\Process\Process;

if (false === in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
    echo 'Warning: The console should be invoked via the CLI version of PHP, not the '.\PHP_SAPI.' SAPI'.\PHP_EOL;
}

set_time_limit(0);

require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__).'/config/bootstrap.php';

$tableToKeep = [
    "oro_access_group",
    "oro_access_role",
    "oro_config",
    "oro_config_value",
    "oro_user",
    "oro_user_access_group",
    "oro_user_access_group_role",
    "oro_user_access_role",
    "pim_session",
];


$kernel = new Kernel($_SERVER['APP_ENV'], false);
$kernel->boot();
$application = new Application($kernel);
$input = new ArgvInput();
$output = new ConsoleOutput();

$container = $kernel->getContainer();
$connection = $container->get('database_connection');
$commandExecutor = new CommandExecutor($input, $output, $application);
$fixtureJobLoader = $container->get('pim_installer.fixture_loader.job_loader');
$eventDispatcher = $container->get('event_dispatcher');
$installTimeQuery = $container->get('Akeneo\Platform\Bundle\InstallerBundle\Persistence\Sql\InstallData');
$clientRegistry = $container->get('akeneo_elasticsearch.registry.clients');

$catalogLoader = new CatalogLoader($clientRegistry, $commandExecutor, $fixtureJobLoader, $eventDispatcher, $installTimeQuery);

$response = $connection->executeQuery("SELECT table_name FROM information_schema.tables WHERE table_schema = 'akeneo_pim'");
$sql = 'SET FOREIGN_KEY_CHECKS = 0;';
foreach ($response->fetchFirstColumn() as $table) {
    if (!in_array($table, $tableToKeep)) {
        $sql .= sprintf('TRUNCATE TABLE %s;', $table);
    }
}

timing("set_foreign_key_check_and_truncate", fn() => $connection->exec($sql));
timing("re-enabled_foreign_key_check", fn() => $connection->exec('SET FOREIGN_KEY_CHECKS = 1'));
timing("load fixture", fn() => $catalogLoader->execute("src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/minimal"));

function timing(string $step, $callback)
{
    $startTime = microtime(true);
    $callback();
    $endTime = microtime(true);
    var_dump($step . ': Take ' . $endTime - $startTime . ' seconds');
}

class CatalogLoader
{
    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly CommandExecutor $commandExecutor,
        private readonly FixtureJobLoader $fixtureJobLoader,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly InstallData $installTimeQuery,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function execute(string $catalogPath): void
    {
        $this->resetElasticsearchIndex();
        $this->setLatestKnownMigration();

        $this->eventDispatcher->dispatch(
            new InstallerEvent($this->commandExecutor, null, ['catalog' => $catalogPath]),
            InstallerEvents::PRE_LOAD_FIXTURES
        );

        $this->loadFixturesStep($catalogPath);

        $this->eventDispatcher->dispatch(
            new InstallerEvent($this->commandExecutor, null, ['catalog' => $catalogPath]),
            InstallerEvents::POST_LOAD_FIXTURES
        );

        $this->installTimeQuery->withDatetime(new \DateTimeImmutable());
    }

    private function resetElasticsearchIndex(): void
    {
        var_dump('<info>Reset elasticsearch indexes</info>');

        $clients = $this->clientRegistry->getClients();

        foreach ($clients as $client) {
            $client->resetIndex();
        }
    }

    private function loadFixturesStep(string $catalogPath): void
    {
        var_dump(sprintf('<info>Load jobs for fixtures. (data set: %s)</info>', $catalogPath));
        $this->fixtureJobLoader->loadJobInstances($catalogPath);

        $jobInstances = $this->fixtureJobLoader->getLoadedJobInstances();
        foreach ($jobInstances as $jobInstance) {
            $params = [
                'code'       => $jobInstance->getCode(),
                '--no-debug' => true,
                '--no-log'   => true,
                '-v'         => true,
            ];

            $this->eventDispatcher->dispatch(
                new InstallerEvent($this->commandExecutor, $jobInstance->getCode(), ['catalog' => $catalogPath]),
                InstallerEvents::PRE_LOAD_FIXTURE
            );

            $this->commandExecutor->runCommand('akeneo:batch:job', $params);
            $this->eventDispatcher->dispatch(
                new InstallerEvent($this->commandExecutor, $jobInstance->getCode(), [
                    'job_name' => $jobInstance->getJobName(),
                    'catalog' => $catalogPath,
                ]),
                InstallerEvents::POST_LOAD_FIXTURE
            );
        }

        var_dump('<info>Delete jobs for fixtures.</info>');
        $this->fixtureJobLoader->deleteJobInstances();
    }

    private function setLatestKnownMigration(): void
    {
        $latestMigration = $this->getLatestMigration();
        $this->commandExecutor->runCommand('doctrine:migrations:sync-metadata-storage', ['-q' => true]);
        $this->commandExecutor->runCommand(
            'doctrine:migrations:version',
            ['version' => $latestMigration, '--add' => true, '--all' => true, '-q' => true]
        );
    }

    private function getLatestMigration(): string
    {
        $params = ['bin/console', 'doctrine:migrations:latest'];

        $params[] = '--no-debug';

        $latestMigrationProcess = new Process($params);
        $latestMigrationProcess->run();

        if ($latestMigrationProcess->getExitCode() !== 0) {
            throw new \RuntimeException("Impossible to get the latest migration {$latestMigrationProcess->getErrorOutput()}");
        }

        return $latestMigrationProcess->getOutput();
    }
}

// Deployer un clone
// category on product page
