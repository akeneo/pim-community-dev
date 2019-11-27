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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationCollectionFactory;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\GetTransformations;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

class SqlGetTransformations implements GetTransformations
{
    /** @var Connection */
    private $connection;

    /** @var TransformationCollectionFactory */
    private $transformationCollectionFactory;

    public function __construct(
        Connection $connection,
        TransformationCollectionFactory $transformationCollectionFactory
    ) {
        $this->connection = $connection;
        $this->transformationCollectionFactory = $transformationCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function fromAssetIdentifiers(array $assetIdentifiers): array
    {
        if (empty($assetIdentifiers)) {
            return [];
        }

        $assetStringIdentifiers = array_unique(array_map(function (AssetIdentifier $assetIdentifier) {
            return $assetIdentifier->__toString();
        }, $assetIdentifiers));

        $sql = <<<SQL
WITH
grouped_asset_family AS (
    SELECT
        asset_family_identifier,
        GROUP_CONCAT(identifier) AS asset_identifiers
    FROM akeneo_asset_manager_asset
    WHERE identifier IN (:identifiers)
    GROUP BY asset_family_identifier
)
SELECT
    grouped_asset_family.asset_family_identifier,
    asset_family.transformations,
    grouped_asset_family.asset_identifiers
FROM grouped_asset_family
    JOIN akeneo_asset_manager_asset_family asset_family ON grouped_asset_family.asset_family_identifier = asset_family.identifier
SQL;

        $rows = $this->connection->executeQuery(
            $sql,
            [
                'identifiers' => $assetStringIdentifiers,
            ],
            [
                'identifiers' => Connection::PARAM_STR_ARRAY,
            ]
        )->fetchAll();

        $transformationsPerAsset = [];
        foreach ($rows as $row) {
            $transformations = Type::getType(Type::JSON_ARRAY)
                ->convertToPHPValue($row['transformations'], $this->connection->getDatabasePlatform());
            $transformationCollection = $this->transformationCollectionFactory->fromNormalized($transformations);

            $assetIdentifiers = explode(',', $row['asset_identifiers']);
            foreach ($assetIdentifiers as $assetIdentifier) {
                $transformationsPerAsset[$assetIdentifier] = $transformationCollection;
            }
        }

        return $transformationsPerAsset;
    }
}
