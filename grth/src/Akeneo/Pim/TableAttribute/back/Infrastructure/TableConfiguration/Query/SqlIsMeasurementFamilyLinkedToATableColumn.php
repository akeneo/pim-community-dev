<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Query;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\IsMeasurementFamilyLinkedToATableColumn;
use Doctrine\DBAL\Connection;

final class SqlIsMeasurementFamilyLinkedToATableColumn implements IsMeasurementFamilyLinkedToATableColumn
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function forCode(string $code): bool
    {
        $query = <<<SQL
            SELECT EXISTS(
                SELECT id FROM pim_catalog_table_column as tc
                INNER JOIN akeneo_measurement as m
                    ON m.code = :code
                    AND tc.properties->"$.measurement_family_code" = m.code
            )
        SQL;

        return (bool) $this->connection->executeQuery(
            $query,
            ['code' => $code]
        )->fetchOne();
    }
}
