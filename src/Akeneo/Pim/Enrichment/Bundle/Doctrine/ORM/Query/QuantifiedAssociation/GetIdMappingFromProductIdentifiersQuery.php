<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Component\Product\Model\QuantifiedAssociation\IdMapping;
use Akeneo\Pim\Enrichment\Component\Product\Query\QuantifiedAssociation\GetIdMappingFromProductIdentifiersQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetIdMappingFromProductIdentifiersQuery implements GetIdMappingFromProductIdentifiersQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(array $productIdentifiers): IdMapping
    {
        if (empty($productIdentifiers)) {
            return IdMapping::createFromMapping([]);
        }

        $query = <<<SQL
        SELECT id, identifier from pim_catalog_product WHERE identifier IN (:product_identifiers)
SQL;

        $mapping = array_column($this->connection->executeQuery(
            $query,
            ['product_identifiers' => $productIdentifiers],
            ['product_identifiers' => Connection::PARAM_STR_ARRAY]
        )->fetchAll(), 'identifier', 'id');

        return IdMapping::createFromMapping($mapping);
    }
}
