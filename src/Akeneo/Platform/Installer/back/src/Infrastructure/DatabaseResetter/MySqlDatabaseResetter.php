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

namespace Akeneo\Platform\Installer\Infrastructure\DatabaseResetter;

use Akeneo\Platform\Installer\Domain\Service\DatabaseResetterInterface;
use Doctrine\DBAL\Connection;

class MySqlDatabaseResetter implements DatabaseResetterInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function reset(array $tableToReset)
    {
        $sql = 'SET FOREIGN_KEY_CHECKS = 0;';
        foreach ($tableToReset as $table) {
            $sql .= sprintf('TRUNCATE TABLE %s;', $table);
        }

        $sql .= 'SET FOREIGN_KEY_CHECKS = 1;';

        $this->connection->executeStatement($sql);
    }
}
