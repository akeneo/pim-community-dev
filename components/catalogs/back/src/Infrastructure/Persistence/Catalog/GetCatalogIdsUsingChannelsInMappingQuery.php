<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogIdsUsingChannelsInMappingQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCatalogIdsUsingChannelsInMappingQuery implements GetCatalogIdsUsingChannelsInMappingQueryInterface
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $channelCodes): array
    {
        $query = <<<SQL
        SELECT DISTINCT BIN_TO_UUID(id)
        FROM akeneo_catalog
        WHERE JSON_OVERLAPS(product_mapping->'$.*.scope', :channelCodes)
            AND is_enabled IS TRUE
        SQL;

        /** @var string[] $catalogIds */
        $catalogIds = $this->connection->executeQuery($query, [
            'channelCodes' => \json_encode($channelCodes, JSON_THROW_ON_ERROR),
        ])->fetchFirstColumn();

        return $catalogIds;
    }
}
