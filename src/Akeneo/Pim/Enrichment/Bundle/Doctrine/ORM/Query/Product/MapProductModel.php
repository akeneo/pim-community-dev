<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\Product;

use Doctrine\DBAL\Connection;

class MapProductModel
{
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function forIds(array $productModelIds)
    {
        return array_column($this->connection->executeQuery('SELECT id, code from pim_catalog_product_model WHERE id IN (:product_model_ids)', [
            'product_model_ids' => $productModelIds
        ], [
            'product_model_ids' => Connection::PARAM_INT_ARRAY
        ])->fetchAll(), 'code', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function forCodes(array $productModelCodes)
    {
        return array_column($this->connection->executeQuery('SELECT id, code from pim_catalog_product_model WHERE code IN (:product_codes)', [
            'product_codes' => $productModelCodes
        ], [
            'product_codes' => Connection::PARAM_STR_ARRAY
        ])->fetchAll(), 'id', 'code');
    }
}
