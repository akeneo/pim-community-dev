<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
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
