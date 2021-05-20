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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset;

use Akeneo\AssetManager\Domain\Query\Asset\FindAssetLabelsByIdentifiersInterface;
use Doctrine\DBAL\Connection;
use PDO;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class SqlFindAssetLabelsByIdentifiers implements FindAssetLabelsByIdentifiersInterface
{
    private Connection $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function find(array $assetIdentifiers): array
    {
        $fetch = <<<SQL
SELECT 
    result.asset_identifier as identifier,
    result.asset_code as code,
    JSON_OBJECTAGG(result.locale_code, result.label) as labels
FROM (
    SELECT 
        labels_result.asset_identifier,
        labels_result.asset_code,
        labels_result.locale_code,
        labels_result.label
    FROM (
        SELECT 
            r.identifier as asset_identifier,
            r.code as asset_code,
            locales.code as locale_code,
            JSON_EXTRACT(
                value_collection,
                CONCAT('$.', '"', re.attribute_as_label, '_', locales.code, '"', '.data')
            ) as label
        FROM akeneo_asset_manager_asset r
        JOIN akeneo_asset_manager_asset_family re
            ON r.asset_family_identifier = re.identifier
        CROSS JOIN pim_catalog_locale as locales
        WHERE locales.is_activated = true
        AND r.identifier IN (:assetIdentifiers)
    ) as labels_result
) as result
GROUP BY identifier;
SQL;

        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            [
                'assetIdentifiers' => $assetIdentifiers,
            ],
            [
                'assetIdentifiers' => Connection::PARAM_STR_ARRAY
            ]
        );

        return array_reduce($statement->fetchAll(PDO::FETCH_ASSOC), function ($labelsIndexedByAsset, $current) {
            $labelsIndexedByAsset[$current['identifier']] = [
                'labels' => json_decode($current['labels'], true),
                'code' => $current['code'],
            ];

            return $labelsIndexedByAsset;
        }, []);
    }
}
