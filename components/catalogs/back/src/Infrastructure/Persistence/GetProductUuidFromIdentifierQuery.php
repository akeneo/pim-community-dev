<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Doctrine\DBAL\Connection;

class GetProductUuidFromIdentifierQuery
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function execute(string $identifier): string
    {
        $sql = <<<SQL
            SELECT BIN_TO_UUID(uuid)
            FROM pim_catalog_product
            WHERE identifier = :identifier
        SQL;

        $uuid = (string) $this->connection->fetchOne($sql, [
            'identifier' => $identifier,
        ]);

        if (!$uuid) {
            throw new \InvalidArgumentException('Unknown identifier');
        }

        return $uuid;
    }
}
