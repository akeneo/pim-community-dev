<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Query;

use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductUuidsFromProductIdentifiers
{
    public function __construct(
        private Connection $db,
    ) {
    }

    /**
     * @param string[] $productIdentifiers
     *
     * @return array<string, UuidInterface>
     */
    public function execute(array $productIdentifiers): array
    {
        $query = <<<SQL
SELECT identifier, BIN_TO_UUID(uuid) AS uuid FROM pim_catalog_product
WHERE identifier IN (:product_identifiers)
SQL;

        $stmt = $this->db->executeQuery(
            $query,
            ['product_identifiers' => $productIdentifiers],
            ['product_identifiers' => Connection::PARAM_STR_ARRAY]
        );

        $productIds = [];
        while ($product = $stmt->fetchAssociative()) {
            $productIds[(string) $product['identifier']] = Uuid::fromString((string) $product['uuid']);
        }

        return $productIds;
    }
}
