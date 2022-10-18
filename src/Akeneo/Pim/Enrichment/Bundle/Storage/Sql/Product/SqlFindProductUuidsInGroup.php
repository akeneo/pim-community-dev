<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Component\Product\Query\FindProductUuidsInGroup;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlFindProductUuidsInGroup implements FindProductUuidsInGroup
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function forGroupId(int $groupId): array
    {
        return $this->connection->fetchFirstColumn(
            <<<SQL
            SELECT BIN_TO_UUID(product_uuid) AS uuid
            FROM pim_catalog_group_product
            WHERE group_id = :groupId
            SQL,
            ['groupId' => $groupId]
        );
    }
}
