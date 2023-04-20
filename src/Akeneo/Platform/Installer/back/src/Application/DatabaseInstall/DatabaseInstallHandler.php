<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Application\DatabaseInstall;

use Akeneo\Platform\Installer\Domain\CommandExecutor\CreateTableInterface;
use Akeneo\Platform\Installer\Domain\CommandExecutor\SetLatestMigrationInterface;
use Akeneo\Platform\Installer\Domain\Event\InstallerEvent;
use Akeneo\Platform\Installer\Domain\Event\InstallerEvents;
use Akeneo\Platform\Installer\Domain\Query\Elasticsearch\ResetIndexesInterface;
use Akeneo\Platform\Installer\Domain\Query\Sql\InsertDatabaseInstallationDateInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class DatabaseInstallHandler
{
    public function __construct(
        private readonly CreateTableInterface $createTable,
        private readonly SetLatestMigrationInterface $setLatestMigration,
        private readonly InsertDatabaseInstallationDateInterface $databaseInstallationDate,
        private readonly ResetIndexesInterface $resetIndexes,
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function handle(DatabaseInstallCommand $command): void
    {
        $io = $command->getIo();

        $this->prepareDatabase($io);

        if (false === $command->getOption('withoutIndexes')) {
            $this->resetIndexes($io);
        }

        $this->entityManager->clear();

        $this->eventDispatcher->dispatch(
            new InstallerEvent(),
            InstallerEvents::POST_DB_CREATE,
        );

        $this->setLatestMigration->setMigration($command->getOption('env'));

        $io->info('Set database installation date');
        $this->databaseInstallationDate->withDateTime(new \DateTimeImmutable());
        $io->success('Installation date set');
    }

    private function prepareDatabase(SymfonyStyle $io): void
    {
        $io->title('Prepare database schema');

        $io->info(sprintf('RUN: %s', $this->createTable->getName()));
        /** @var BufferedOutput $output */
        $output = $this->createTable->execute(['--force' => true, '--no-interaction' => true], true);
        $io->block($output->fetch());
        $io->success(sprintf('%s success', $this->createTable->getName()));
    }

    private function resetIndexes(SymfonyStyle $io): void
    {
        $io->info('Reset elasticsearch indexes');
        $this->resetIndexes->reset();
        $io->success('Indexes has been reset');
    }
}
