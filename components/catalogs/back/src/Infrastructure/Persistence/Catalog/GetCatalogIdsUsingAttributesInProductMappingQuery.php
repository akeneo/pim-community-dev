<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogIdsUsingAttributesInProductMappingQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCatalogIdsUsingAttributesInProductMappingQuery implements GetCatalogIdsUsingAttributesInProductMappingQueryInterface
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * {@inheritdoc}
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \JsonException
     */
    public function execute(array $attributeCodes): array
    {
        $query = <<<SQL
        SELECT DISTINCT BIN_TO_UUID(id)
        FROM akeneo_catalog
        WHERE JSON_OVERLAPS(JSON_EXTRACT(product_mapping, '$.*.source'), :attributeCodes)
            AND is_enabled IS TRUE
        SQL;

        /** @var string[] $catalogIds */
        $catalogIds = $this->connection->executeQuery($query, [
            'attributeCodes' => \json_encode($attributeCodes, JSON_THROW_ON_ERROR),
        ])->fetchFirstColumn();

        return $catalogIds;
    }
}
