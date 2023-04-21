<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\CommandExecutor;

use Akeneo\Platform\Installer\Domain\CommandExecutor\SetLatestMigrationInterface;
use Symfony\Component\Console\Output\BufferedOutput;

final class SetLatestMigration implements SetLatestMigrationInterface
{
    public function __construct(
        private readonly DoctrineMigrationsVersionInterface $doctrineMigrationsVersion,
        private readonly DoctrineMigrationsSyncMetadataStorageInterface $doctrineMigrationsSyncMetadataStorage,
        private readonly DoctrineMigrationsLatestInterface $doctrineMigrationsLatest,
    ) {
    }

    public function setMigration(string $env): void
    {
        /** @var BufferedOutput $output */
        $output = $this->doctrineMigrationsLatest->execute([
            '--no-debug' => true,
            '--env' => $env,
        ], true);
        $latestMigration = $output->fetch();

        $this->doctrineMigrationsSyncMetadataStorage->execute(['-q' => true]);

        $this->doctrineMigrationsVersion->execute([
            'version' => $latestMigration,
            '--add' => true,
            '--all' => true,
            '-q' => true,
        ]);
    }
}
