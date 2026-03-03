<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetExistingProductUuids
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * @param string[] $identifiers
     */
    public function among(array $identifiers): array
    {
        Assert::allString($identifiers);
        $sql = <<<SQL
SELECT product_uuid AS uuid
FROM pim_catalog_product_unique_data
INNER JOIN pim_catalog_attribute ON pim_catalog_attribute.id = pim_catalog_product_unique_data.attribute_id
WHERE raw_data IN (:identifiers)
AND main_identifier = 1;
SQL;

        $uuids = $this->connection->executeQuery(
            $sql,
            ['identifiers' => $identifiers],
            ['identifiers' => Connection::PARAM_STR_ARRAY]
        )->fetchFirstColumn();

        return array_map(static fn (string $uuid): UuidInterface => Uuid::fromBytes($uuid), $uuids);
    }
}
