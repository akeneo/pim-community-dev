<?php

namespace Akeneo\SharedCatalog\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

class GetProductIdFromProductIdentifierQuery implements GetProductIdFromProductIdentifierQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
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
