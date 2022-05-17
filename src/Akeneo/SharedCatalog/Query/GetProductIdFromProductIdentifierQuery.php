<?php

namespace Akeneo\SharedCatalog\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

class GetProductIdFromProductIdentifierQuery implements GetProductIdFromProductIdentifierQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function execute(string $productIdentifier): ?string
    {
        $sql = <<<SQL
SELECT id FROM pim_catalog_product
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

        return $statement->fetchColumn() ?: null;
    }
}
