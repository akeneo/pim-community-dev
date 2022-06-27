<?php

namespace Akeneo\SharedCatalog\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class GetProductUuidFromProductIdentifierQuery implements GetProductUuidFromProductIdentifierQueryInterface
{
    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function execute(string $productIdentifier): ?UuidInterface
    {
        $sql = <<<SQL
SELECT BIN_TO_UUID(uuid) FROM pim_catalog_product
WHERE identifier = :product_identifier
SQL;

        $statement = $this->connection->executeQuery(
            $sql,
            [
                'product_identifier' => $productIdentifier,
            ],
            [
                'product_identifier' => Types::STRING,
            ]
        );
        $uuidAsString = $statement->fetchOne();

        return $uuidAsString ? Uuid::fromString($uuidAsString) : null;
    }
}
