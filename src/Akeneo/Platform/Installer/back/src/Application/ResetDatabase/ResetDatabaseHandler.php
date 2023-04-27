<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Application\ResetDatabase;

use Akeneo\Platform\Installer\Domain\Query\FindTablesInterface;
use Akeneo\Platform\Installer\Domain\Service\DatabaseResetterInterface;

class ResetDatabaseHandler
{
    public function __construct(
        private readonly FindTablesInterface $findTableUsed,
        private readonly DatabaseResetterInterface $databaseResetter
    ) {
    }

    public function handle(ResetDatabaseCommand $command): void
    {
        $tables = $this->findTableUsed->all();
        $tablesToReset = array_filter($tables, fn (string $table) => !in_array($table, $command->tablesToKeep));

        $this->databaseResetter->reset($tablesToReset);
    }
}
