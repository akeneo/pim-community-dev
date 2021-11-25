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

namespace Oro\Bundle\PimDataGridBundle\Query\Sql;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * Pull-up master/6.0: do not pull this class: a migration handles the remove.
 */
final class RemoveUniqueLabelConstraint
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function removeIfExists(): void
    {
        $databaseNameSql = 'SELECT database()';
        $databaseName = $this->connection->executeQuery($databaseNameSql)->fetch(FetchMode::COLUMN);
        if (!is_string($databaseName)) {
            return;
        }

        $findConstraintNameSql = <<< SQL
        SELECT DISTINCT CONSTRAINT_NAME
        FROM information_schema.TABLE_CONSTRAINTS
        WHERE table_name = 'pim_datagrid_view' AND constraint_type = 'UNIQUE' AND TABLE_SCHEMA = :database_name;
        SQL;

        $uniqueConstraintName = $this->connection->executeQuery($findConstraintNameSql, [
            'database_name' => $databaseName,
        ])->fetch(FetchMode::COLUMN);
        if (!is_string($uniqueConstraintName)) {
            return;
        }

        $dropIndexSql = sprintf('ALTER TABLE pim_datagrid_view DROP index %s', $uniqueConstraintName);
        $this->connection->executeQuery($dropIndexSql, ['index_name' => $uniqueConstraintName]);
    }
}
