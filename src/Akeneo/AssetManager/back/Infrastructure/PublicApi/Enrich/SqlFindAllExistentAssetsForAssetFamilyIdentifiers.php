<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\PublicApi\Enrich;

use Doctrine\DBAL\Connection;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class SqlFindAllExistentAssetsForAssetFamilyIdentifiers
{
    private const BATCH_SIZE = 200;

    public function __construct(private Connection $connection)
    {
    }

    public function forAssetFamilyIdentifiersAndAssetCodes(array $assetFamilyIdentifiersToCodes): array
    {
        $queryParams = [];
        foreach ($assetFamilyIdentifiersToCodes as $assetFamilyIdentifier => $assetCodes) {
            foreach ($assetCodes as $assetCode) {
                $queryParams[] = $assetFamilyIdentifier;
                $queryParams[] = $assetCode;
            }
        }

        $chunkedQueryParams = \array_chunk($queryParams, 2 * self::BATCH_SIZE);
        $results = [];
        foreach ($chunkedQueryParams as $queryParamsChunk) {
            $batchedResults = $this->batchedQuery($queryParamsChunk);
            $results = \array_merge_recursive($results, $batchedResults);
        }

        return $results;
    }

    /**
     * @param string[] $queryParams
     * @return array<string, string[]>
     */
    private function batchedQuery(array $queryParams): array
    {
        if (empty($queryParams)) {
            return [];
        }

        $query = <<<SQL
        SELECT /*+ SET_VAR( range_optimizer_max_mem_size = 50000000) */ asset_family_identifier as asset_family_identifier, JSON_ARRAYAGG(code) as asset_codes
        FROM akeneo_asset_manager_asset
        WHERE (asset_family_identifier, code) IN (%s)
        GROUP BY asset_family_identifier;
        SQL;

        /**
         * We have to build the query by hand because Doctrine does not support tuple for IN (:myParameter) things
         * https://www.doctrine-project.org/projects/doctrine-dbal/en/2.9/reference/data-retrieval-and-manipulation.html#list-of-parameters-conversion
         */
        $queryStringParams = \array_fill(0, count($queryParams) / 2, '(?, ?)');

        $rawResults = $this->connection->executeQuery(
            \sprintf($query, \implode(',', $queryStringParams)),
            $queryParams
        )->fetchAllAssociative();

        return \array_reduce($rawResults, function (array $results, array $item) {
            $results[$item['asset_family_identifier']] = \json_decode($item['asset_codes'], true);

            return $results;
        }, []);
    }
}
