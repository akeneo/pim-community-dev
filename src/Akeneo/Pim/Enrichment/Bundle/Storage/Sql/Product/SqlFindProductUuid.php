<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Product;

use Akeneo\Pim\Enrichment\Component\Product\Query\FindId;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlFindProductUuid implements FindId
{
    public function __construct(private Connection $connection)
    {
    }

    public function fromIdentifier(string $identifier): null|string
    {
        $uuid = $this->connection->executeQuery(<<<SQL
SELECT BIN_TO_UUID(product_uuid) AS uuid
FROM pim_catalog_product_unique_data
INNER JOIN pim_catalog_attribute ON pim_catalog_product_unique_data.attribute_id = pim_catalog_attribute.id
WHERE raw_data = :identifier
AND pim_catalog_attribute.main_identifier = 1
SQL,
            ['identifier' => $identifier]
        )->fetchOne();

        return false === $uuid ? null : (string)$uuid;
    }
}
