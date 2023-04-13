<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Application\DatabaseInstall;

use Akeneo\Platform\Installer\Domain\Query\CommandExecutor\DoctrineSchemaUpdateInterface;
use Akeneo\Platform\Installer\Domain\Query\Elasticsearch\ResetIndexesInterface;
use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvent;
use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class DatabaseInstallHandler
{
    public function __construct(
        private readonly DoctrineSchemaUpdateInterface $doctrineSchemaUpdate,
        private readonly ResetIndexesInterface $resetIndexes,
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcher $eventDispatcher
    ) {}

    public function handle(DatabaseInstallCommand $command): void
    {
        $io = $command->getIo();

        $io->title('Prepare database schema');

        try {
            $io->info('Update schema');
            $this->doctrineSchemaUpdate->execute();
            $io->success('Schema updated');
        } catch (\Exception $e) {
            $io->error([
                'Trying to install PIM on an existing database is impossible.',
                $e->getMessage()
            ]);
        }

        if (false === $command->getOptions()['withoutIndexes']) {
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

        $this->entityManager->clear();

        // TODO check if a listener still need catalog don't seems like it
        $this->eventDispatcher->dispatch(
            new InstallerEvent(null, [
                'catalog' => $command->getOptions()['catalog'],
            ]),
            InstallerEvents::POST_DB_CREATE
        );
    }
}
