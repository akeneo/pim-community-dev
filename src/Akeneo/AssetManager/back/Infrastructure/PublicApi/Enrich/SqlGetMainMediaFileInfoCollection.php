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

use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
final class SqlGetMainMediaFileInfoCollection implements GetMainMediaFileInfoCollectionInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function forAssetFamilyAndAssetCodes(
        string $assetFamilyIdentifier,
        array $assetCodes
    ): array {
        if (0 === count($assetCodes)) {
            return [];
        }

        Assert::allString($assetCodes);

        $sql = <<<SQL
SELECT
    asset.code,
    asset.value_collection,
    family.attribute_as_main_media
FROM akeneo_asset_manager_asset asset
    JOIN akeneo_asset_manager_asset_family family ON asset.asset_family_identifier = family.identifier
    JOIN akeneo_asset_manager_attribute attribute ON family.attribute_as_main_media = attribute.identifier
WHERE asset.asset_family_identifier = :assetFamilyIdentifier AND asset.code IN (:assetCodes)
AND attribute.attribute_type = 'media_file'
SQL;

        $rawResults = $this->connection->executeQuery(
            $sql,
            ['assetFamilyIdentifier' => $assetFamilyIdentifier, 'assetCodes' => $assetCodes],
            ['assetCodes' => Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        $mediaFileInfoCollection = [];
        foreach ($rawResults as $rawResult) {
            $mainMediaValues = $this->filterMainMediaValues(
                json_decode($rawResult['value_collection'], true),
                $rawResult['attribute_as_main_media']
            );

            $mediaFileInfoCollection = array_merge($mediaFileInfoCollection, array_map(static fn ($mainMediaValue) => new MediaFileInfo(
                $mainMediaValue['data']['filePath'],
                $mainMediaValue['data']['originalFilename'],
                Storage::FILE_STORAGE_ALIAS
            ), $mainMediaValues));
        }

        return $mediaFileInfoCollection;
    }

    private function filterMainMediaValues(array $rawValueCollection, string $attributeAsMainMediaIdentifier): array
    {
        return array_values(array_filter(
            $rawValueCollection,
            static fn (array $value) => $value['attribute'] === $attributeAsMainMediaIdentifier
                && null !== $value['data']['filePath'] ?? null
                && null !== $value['data']['originalFilename'] ?? null
        ));
    }
}
