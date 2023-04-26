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

namespace Akeneo\Platform\Installer\Infrastructure\Query;

use Akeneo\Platform\Installer\Domain\Query\FindTablesInterface;
use Doctrine\DBAL\Connection;

class FindTables implements FindTablesInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function all(): array
    {
        return $this->connection->executeQuery(
            "SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE()",
        )->fetchFirstColumn();
    }
}
