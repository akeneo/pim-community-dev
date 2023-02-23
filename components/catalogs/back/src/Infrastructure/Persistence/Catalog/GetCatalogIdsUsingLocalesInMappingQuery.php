<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogIdsUsingLocalesInMappingQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCatalogIdsUsingLocalesInMappingQuery implements GetCatalogIdsUsingLocalesInMappingQueryInterface
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
    public function execute(array $localeCodes): array
    {
        $query = <<<SQL
        SELECT DISTINCT BIN_TO_UUID(id)
        FROM akeneo_catalog
        WHERE JSON_OVERLAPS(product_mapping->'$.*.locale', :localeCodes)
            AND is_enabled IS TRUE
        SQL;

        /** @var string[] $catalogIds */
        $catalogIds = $this->connection->executeQuery($query, [
            'localeCodes' => \json_encode($localeCodes, JSON_THROW_ON_ERROR),
        ])->fetchFirstColumn();

        return $catalogIds;
    }
}
