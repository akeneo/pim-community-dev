<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\UuidMapping;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductUuidsQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetIdMappingFromProductUuidsQuery implements GetIdMappingFromProductUuidsQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(array $productUuids): UuidMapping
    {
        if (empty($productUuids)) {
            return UuidMapping::createFromMapping([]);
        }

        $query = <<<SQL
        SELECT BIN_TO_UUID(uuid) as uuid, identifier from pim_catalog_product WHERE uuid IN (:product_uuids)
SQL;

        $mapping = array_column($this->connection->executeQuery(
            $query,
            ['product_uuids' => $productUuids],
            ['product_uuids' => Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative(), 'identifier', 'uuid');

        return UuidMapping::createFromMapping($mapping);
    }
}
