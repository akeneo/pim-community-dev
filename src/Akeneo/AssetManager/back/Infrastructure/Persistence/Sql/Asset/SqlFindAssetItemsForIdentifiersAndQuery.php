<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset;

use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetItemsForIdentifiersAndQueryInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\BulkAssetItemHydrator;
use Doctrine\DBAL\Connection;

/**
 *
 * Find asset items for the given asset identifiers & the given asset query.
 * Note that this query searches only assets with the same asset family.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindAssetItemsForIdentifiersAndQuery implements FindAssetItemsForIdentifiersAndQueryInterface
{
    public function __construct(
        private Connection $sqlConnection,
        private BulkAssetItemHydrator $bulkAssetItemHydrator
    ) {
    }

    public function find(array $identifiers, AssetQuery $query): array
    {
        $normalizedAssetItems = $this->fetchAll($identifiers);
        $orderedAssetItems = $this->orderAssetItems($normalizedAssetItems, $identifiers);

        return $this->bulkAssetItemHydrator->hydrateAll($orderedAssetItems, $query);
    }

    private function fetchAll(array $identifiers): array
    {
        $sqlQuery = <<<SQL
        SELECT
            /*+ SET_VAR( range_optimizer_max_mem_size = 50000000) */
            asset.identifier,
            asset.asset_family_identifier,
            asset.code,
            asset.value_collection,
            reference.attribute_as_main_media,
            reference.attribute_as_label
        FROM akeneo_asset_manager_asset AS asset
        INNER JOIN akeneo_asset_manager_asset_family AS reference
            ON reference.identifier = asset.asset_family_identifier
        WHERE asset.identifier IN (:identifiers);
SQL;

        $statement = $this->sqlConnection->executeQuery($sqlQuery, [
            'identifiers' => $identifiers,
        ], ['identifiers' => Connection::PARAM_STR_ARRAY]);

        return $statement->fetchAllAssociative();
    }

    private function orderAssetItems(array $normalizedAssetItems, array $orderedIdentifiers): array
    {
        $resultIndexedByIdentifier = array_column($normalizedAssetItems, null, 'identifier');
        $resultIndexedByIdentifier = array_change_key_case($resultIndexedByIdentifier, CASE_LOWER);

        $existingIdentifiers = [];
        foreach ($orderedIdentifiers as $orderedIdentifier) {
            $sanitizedIdentifier = trim(strtolower($orderedIdentifier));

            if (isset($resultIndexedByIdentifier[$sanitizedIdentifier])) {
                $existingIdentifiers[$sanitizedIdentifier] = $sanitizedIdentifier;
            }
        }

        $result = array_replace($existingIdentifiers, $resultIndexedByIdentifier);

        return array_values($result);
    }
}
