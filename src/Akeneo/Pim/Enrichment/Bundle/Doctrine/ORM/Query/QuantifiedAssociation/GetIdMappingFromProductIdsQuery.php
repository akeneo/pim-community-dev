<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\IdMapping;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductIdsQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetIdMappingFromProductIdsQuery implements GetIdMappingFromProductIdsQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(array $productIds): IdMapping
    {
        if (empty($productIds)) {
            return IdMapping::createFromMapping([]);
        }

        $query = <<<SQL
        SELECT id, identifier from pim_catalog_product WHERE id IN (:product_ids)
SQL;

        $mapping = array_column($this->connection->executeQuery(
            $query,
            ['product_ids' => $productIds],
            ['product_ids' => Connection::PARAM_INT_ARRAY]
        )->fetchAll(), 'identifier', 'id');

        return IdMapping::createFromMapping($mapping);
    }
}
