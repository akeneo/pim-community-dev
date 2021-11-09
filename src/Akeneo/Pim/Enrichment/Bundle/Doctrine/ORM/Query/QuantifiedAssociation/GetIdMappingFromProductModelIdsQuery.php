<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\IdMapping;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductModelIdsQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetIdMappingFromProductModelIdsQuery implements GetIdMappingFromProductModelIdsQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(array $productModelIds): IdMapping
    {
        if (empty($productModelIds)) {
            return IdMapping::createFromMapping([]);
        }

        $query = <<<SQL
        SELECT id, code from pim_catalog_product_model WHERE id IN (:product_model_ids)
SQL;

        $mapping = array_column($this->connection->executeQuery(
            $query,
            ['product_model_ids' => $productModelIds],
            ['product_model_ids' => Connection::PARAM_INT_ARRAY]
        )->fetchAllAssociative(), 'code', 'id');

        return IdMapping::createFromMapping($mapping);
    }
}
