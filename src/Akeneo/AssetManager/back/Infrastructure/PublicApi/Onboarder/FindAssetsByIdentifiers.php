<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\PublicApi\Onboarder;

use Doctrine\DBAL\Connection;

/**
 * @author    Quentin Favrie <quentin.favrie@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class FindAssetsByIdentifiers
{
    private Connection $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function find(array $assetIdentifiers): \Iterator
    {
        if (empty($assetIdentifiers)) {
            return new \EmptyIterator();
        }

        $fetch = <<<SQL
            SELECT
                result.identifier,
                result.code,
                result.asset_family_identifier,
                result.asset_family_attribute_as_main_media,
                result.attribute_type,
                result.media_type,
                JSON_OBJECTAGG(result.locale_code, result.label) as labels,
                result.value_collection
            FROM (
                SELECT
                    asset.identifier,
                    asset.code,
                    asset_family.identifier AS asset_family_identifier,
                    asset_family.attribute_as_main_media AS asset_family_attribute_as_main_media,
                    attribute.attribute_type as attribute_type,
                    JSON_UNQUOTE(JSON_EXTRACT(attribute.additional_properties, '$.media_type')) AS media_type,
                    locales.code AS locale_code,
                    JSON_EXTRACT(
                        asset.value_collection,
                        CONCAT('$.', '"', asset_family.attribute_as_label, '_', locales.code, '"', '.data')
                    ) AS label,
                    asset.value_collection AS value_collection
                FROM akeneo_asset_manager_asset asset
                JOIN akeneo_asset_manager_asset_family asset_family
                    ON asset.asset_family_identifier = asset_family.identifier
                JOIN akeneo_asset_manager_attribute attribute
                    ON attribute.identifier = asset_family.attribute_as_main_media
                CROSS JOIN pim_catalog_locale AS locales
                WHERE locales.is_activated = true
                AND asset.identifier IN (:assetIdentifiers)
            ) AS result
            GROUP BY identifier;
SQL;

        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            [
                'assetIdentifiers' => $assetIdentifiers,
            ],
            [
                'assetIdentifiers' => Connection::PARAM_STR_ARRAY,
            ]
        );

        foreach ($statement->fetchAll() as $asset) {
            yield new Asset(
                $asset['identifier'],
                json_decode($asset['labels'], true),
                $asset['code'],
                $asset['asset_family_identifier'],
                array_filter(json_decode($asset['value_collection'], true), fn(array $value) => $value['attribute'] === $asset['asset_family_attribute_as_main_media']),
                $asset['attribute_type'],
                $asset['media_type']
            );
        }
    }
}
