<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
