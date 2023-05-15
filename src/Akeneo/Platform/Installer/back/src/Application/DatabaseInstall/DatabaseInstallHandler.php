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
use Psr\Log\LoggerInterface;
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
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(DatabaseInstallCommand $command): void
    {
        $this->logger->info('Prepare database schema');

        $this->createTable->execute(['--force' => true, '--no-interaction' => true], true); // TODO : remove options

        if ($command->withIndexes) {
            $this->logger->info('Reset elasticsearch indexes');
            $this->resetIndexes();
        }

        $this->entityManager->clear();

        $this->eventDispatcher->dispatch(
            new InstallerEvent(),
            InstallerEvents::POST_DB_CREATE,
        );

        $this->setLatestMigration->setMigration($command->environment);
        $this->databaseInstallationDate->withDateTime(new \DateTimeImmutable());
    }

    private function resetIndexes(): void
    {
        $this->resetIndexes->reset();
    }
}
