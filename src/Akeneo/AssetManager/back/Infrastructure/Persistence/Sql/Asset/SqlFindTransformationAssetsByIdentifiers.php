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

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationAsset;
use Akeneo\AssetManager\Domain\Query\Asset\FindTransformationAssetsByIdentifiersInterface;
use Doctrine\DBAL\Connection;

class SqlFindTransformationAssetsByIdentifiers implements FindTransformationAssetsByIdentifiersInterface
{
    private Connection $sqlConnection;

    public function __construct(
        Connection $connection
    ) {
        $this->sqlConnection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function find(array $assetIdentifiers): array
    {
        $sql = <<<SQL
            SELECT 
                identifier, 
                code, 
                asset_family_identifier, 
                value_collection
            FROM akeneo_asset_manager_asset
            WHERE identifier IN (:identifiers);
SQL;

        $statement = $this->sqlConnection->executeQuery(
            $sql,
            ['identifiers' => $assetIdentifiers],
            ['identifiers' => Connection::PARAM_STR_ARRAY]
        );
        $rawResults = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $results = [];
        foreach ($rawResults as $rawResult) {
            $results[$rawResult['identifier']] = new TransformationAsset(
                AssetIdentifier::fromString($rawResult['identifier']),
                AssetCode::fromString($rawResult['code']),
                AssetFamilyIdentifier::fromString($rawResult['asset_family_identifier']),
                json_decode($rawResult['value_collection'], true)
            );
        }

        return $results;
    }
}
