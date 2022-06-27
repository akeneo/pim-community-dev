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

namespace Akeneo\Pim\Permission\Bundle\Persistence\Sql;

use Akeneo\Pim\Permission\Component\Query\GetRawValues;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;

final class SqlGetRawValues implements GetRawValues
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritDoc}
     */
    public function forProductUuid(UuidInterface $uuid): ?array
    {
        $query = <<<SQL
        SELECT
            JSON_MERGE_PATCH(
                COALESCE(pm2.raw_values, '{}'),
                COALESCE(pm1.raw_values, '{}'),
                p.raw_values
            ) as raw_values
        FROM pim_catalog_product p
            LEFT JOIN pim_catalog_product_model pm1 ON p.product_model_id = pm1.id
            LEFT JOIN pim_catalog_product_model pm2 ON pm1.parent_id = pm2.id
        WHERE p.uuid = :uuid
        SQL;

        $rawValues = $this->connection->executeQuery($query, ['uuid' => $uuid->getBytes()])->fetchOne();

        return false === $rawValues ? null : \json_decode($rawValues, true);
    }

    /**
     * {@inheritDoc}
     */
    public function forProductModelId(int $id): ?array
    {
        $query = <<<SQL
        SELECT
            pm.id,
            JSON_MERGE_PATCH(COALESCE(root.raw_values, '{}'), pm.raw_values) as raw_values
        FROM pim_catalog_product_model pm
            LEFT JOIN pim_catalog_product_model root ON pm.parent_id = root.id
        WHERE pm.id = :id
        SQL;

        $row = $this->connection->executeQuery($query, ['id' => $id])->fetch();

        return !$row ? null : \json_decode($row['raw_values'], true);
    }
}
