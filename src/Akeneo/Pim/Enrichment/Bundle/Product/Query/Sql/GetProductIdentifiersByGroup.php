<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductIdentifiersByGroupInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

/**
 * Query to fetch product identifiers linked to a group
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductIdentifiersByGroup implements GetProductIdentifiersByGroupInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return string[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function fetchByGroupId(int $groupId): array
    {
        $sql = 'SELECT t0.identifier 
            FROM pim_catalog_product t0 
            INNER JOIN pim_catalog_group_product ON t0.id = pim_catalog_group_product.product_id 
            WHERE pim_catalog_group_product.group_id = :groupId';

        return $this->connection->executeQuery(
            $sql,
            ['groupId' => $groupId],
            [Connection::PARAM_INT_ARRAY]
        )->fetchAll(FetchMode::COLUMN);
    }
}
