<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\Product;

use Doctrine\DBAL\Connection;

class MapProduct
{
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function forIds(array $productIds)
    {
        return array_column($this->connection->executeQuery('SELECT id, identifier from pim_catalog_product WHERE id IN (:product_ids)', [
            'product_ids' => $productIds
        ], [
            'product_ids' => Connection::PARAM_INT_ARRAY
        ])->fetchAll(), 'identifier', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function forIdentifiers(array $productIdentifiers)
    {
        return array_column($this->connection->executeQuery('SELECT id, identifier from pim_catalog_product WHERE identifier IN (:product_identifiers)', [
            'product_identifiers' => $productIdentifiers
        ], [
            'product_identifiers' => Connection::PARAM_STR_ARRAY
        ])->fetchAll(), 'id', 'identifier');
    }
}
