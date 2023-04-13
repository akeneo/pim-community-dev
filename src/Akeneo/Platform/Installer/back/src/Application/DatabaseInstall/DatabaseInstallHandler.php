<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Application\DatabaseInstall;

use Akeneo\Platform\Installer\Domain\Query\Elasticsearch\ResetIndexesInterface;

final class DatabaseInstallHandler
{
    public function __construct(
        private readonly ResetIndexesInterface $resetIndexes
    ) {}

    public function handle(DatabaseInstallCommand $command): void
    {
        $io = $command->getIo();

        $io->title('Prepare database schema');
    }
}
