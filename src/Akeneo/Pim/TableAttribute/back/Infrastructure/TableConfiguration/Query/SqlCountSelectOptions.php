<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Query;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\CountSelectOptions;
use Doctrine\DBAL\Connection;

final class SqlCountSelectOptions implements CountSelectOptions
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function all(): int
    {
        return (int) $this->connection->executeQuery(
            'SELECT COUNT(*) FROM pim_catalog_table_column_select_option'
        )->fetchColumn();
    }
}
