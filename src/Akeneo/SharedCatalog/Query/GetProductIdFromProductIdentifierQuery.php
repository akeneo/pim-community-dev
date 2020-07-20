<?php

namespace Akeneo\SharedCatalog\Query;

use Doctrine\DBAL\Connection;

class GetProductIdFromProductIdentifierQuery
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
WHERE identifier = (:product_identifier)
SQL;

        $statement = $this->connection->executeQuery(
            $sql,
            [
                'product_identifier' => $productIdentifier,
            ],
            [
                'product_identifiers' => Connection::PARAM_STR_ARRAY,
            ]
        );

        return $statement->fetchColumn() ?: null;
    }
}
