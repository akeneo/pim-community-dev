<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Application\DatabaseInstall;

use Akeneo\Platform\Installer\Domain\Query\CommandExecutor\DoctrineMigrationsLatestInterface;
use Akeneo\Platform\Installer\Domain\Query\CommandExecutor\DoctrineMigrationsSyncMetadataStorageInterface;
use Akeneo\Platform\Installer\Domain\Query\CommandExecutor\DoctrineMigrationsVersionInterface;
use Akeneo\Platform\Installer\Domain\Query\CommandExecutor\DoctrineSchemaUpdateInterface;
use Akeneo\Platform\Installer\Domain\Query\Elasticsearch\ResetIndexesInterface;
use Akeneo\Platform\Installer\Domain\Query\Sql\InsertDatabaseInstallationDateInterface;
use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvent;
use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class DatabaseInstallHandler
{
    public function __construct(
        private readonly DoctrineSchemaUpdateInterface $doctrineSchemaUpdate,
        private readonly DoctrineMigrationsVersionInterface $doctrineMigrationsVersion,
        private readonly DoctrineMigrationsSyncMetadataStorageInterface $doctrineMigrationsSyncMetadataStorage,
        private readonly DoctrineMigrationsLatestInterface $doctrineMigrationsLatest,
        private readonly InsertDatabaseInstallationDateInterface $databaseInstallationDate,
        private readonly ResetIndexesInterface $resetIndexes,
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcher $eventDispatcher
    ) {}

    public function handle(DatabaseInstallCommand $command): void
    {
        $io = $command->getIo();

        $this->prepareDatabase($io);

        if (false === $command->getOptions()['withoutIndexes']) {
            $this->resetIndexes($io);
        }

        $this->entityManager->clear();

        // TODO check if a listener still need catalog don't seems like it
        // TODO recheck all query make idempotent the one that are not
        $this->eventDispatcher->dispatch(
            new InstallerEvent(),
            InstallerEvents::POST_DB_CREATE
        );

        $this->setLatestKnownMigration($io, $command->getOptions()['env']);

        $io->info('Set database installation date');
        $this->databaseInstallationDate->withDateTime(new \DateTimeImmutable());
        $io->success('Installation date set');
    }

    private function prepareDatabase(SymfonyStyle $io): void
    {
        $io->title('Prepare database schema');

        try {
            $io->info(sprintf('RUN: %s', $this->doctrineSchemaUpdate->getName()));
            /** @var BufferedOutput $output */
            $output = $this->doctrineSchemaUpdate->execute(['--force' => true, '--no-interaction' => true], true);
            $io->block($output->fetch());
            $io->success(sprintf('%s success', $this->doctrineSchemaUpdate->getName()));
        } catch (\Exception $e) {
            $io->error([
                'Trying to install PIM on an existing database is impossible.',
                $e->getMessage()
            ]);
        }
    }

    private function resetIndexes(SymfonyStyle $io): void
    {
        try {
            $io->info('Reset elasticsearch indexes');
            $this->resetIndexes->reset();
            $io->success('Indexes has been reset');
        } catch (\Exception $e) {
            $io->error([
                'Something went wrong during index reset',
                $e->getMessage()
            ]);
        }
    }

    private function setLatestKnownMigration(SymfonyStyle $io, string $env): void
    {
        try {
            $io->info(sprintf('RUN: %s', $this->doctrineMigrationsLatest->getName()));
            /** @var BufferedOutput $output */
            $output = $this->doctrineMigrationsLatest->execute([
                '--no-debug' => true,
                '--env' => $env
            ], true);
            $latestMigration = $output->fetch();
            $io->block($latestMigration);
            $io->success(sprintf('%s success', $this->doctrineMigrationsLatest->getName()));

            $io->info(sprintf('RUN: %s', $this->doctrineMigrationsSyncMetadataStorage->getName()));
            /** @var BufferedOutput $output */
            $output = $this->doctrineMigrationsSyncMetadataStorage->execute(['-q' => true], true);
            $io->block($output->fetch());
            $io->success(sprintf('%s success', $this->doctrineMigrationsSyncMetadataStorage->getName()));

            $io->info(sprintf('RUN: %s', $this->doctrineMigrationsVersion->getName()));
            /** @var BufferedOutput $output */
            $output = $this->doctrineMigrationsVersion->execute([
                'version' => $latestMigration,
                '--add' => true,
                '--all' => true,
                '-q' => true
            ], true);
            $io->block($output->fetch());
            $io->success(sprintf('%s success', $this->doctrineMigrationsVersion->getName()));
        } catch (\Exception $e) {
            $io->error([
                'Something went wrong when setting latest migration',
                $e->getMessage()
            ]);
        }
    }
}
