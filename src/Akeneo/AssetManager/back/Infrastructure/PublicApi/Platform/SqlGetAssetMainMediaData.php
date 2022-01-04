<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\PublicApi\Platform;

use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

class SqlGetAssetMainMediaData implements GetAssetMainMediaDataInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function forAssetFamilyAndAssetCodes(string $assetFamilyIdentifier, array $assetCodes, ?string $channel, ?string $locale): array
    {
        if (empty($assetCodes)) {
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
WHERE asset.asset_family_identifier = :assetFamilyIdentifier AND 
      asset.code IN (:assetCodes)
SQL;

        $rawResults = $this->connection->executeQuery(
            $sql,
            ['assetFamilyIdentifier' => $assetFamilyIdentifier, 'assetCodes' => $assetCodes],
            ['assetCodes' => Connection::PARAM_STR_ARRAY]
        )->fetchAllAssociative();

        $assetMainMediaData = [];
        foreach ($rawResults as $rawResult) {
            $assetMainMediaDataValue = $this->filterMainMediaValues(
                json_decode($rawResult['value_collection'], true),
                $rawResult['attribute_as_main_media'],
                $channel,
                $locale
            );
            if (empty($assetMainMediaDataValue)) {
                continue;
            }
            $assetMainMediaData[$rawResult['code']] = $this->extractDataFromMainMediaValue($assetMainMediaDataValue[0]);
        }

        return $assetMainMediaData;
    }

    private function filterMainMediaValues(array $rawValueCollection, string $attributeAsMainMediaIdentifier, ?string $channel, ?string $locale): array
    {
        return array_values(array_filter(
            $rawValueCollection,
            static fn (array $value) =>
                $attributeAsMainMediaIdentifier === $value['attribute'] &&
                $channel == $value['channel'] &&
                $locale == $value['locale']
        ));
    }

    private function extractDataFromMainMediaValue(array $mainMediaValue)
    {
        $mainMediaValueData = $mainMediaValue['data'];
        return is_array($mainMediaValueData) ?
            [
                'filePath' => $mainMediaValueData['filePath'],
                'fileKey' => $mainMediaValueData['filePath'],
                'originalFilename' => $mainMediaValueData['originalFilename']
            ]
            :
            $mainMediaValueData;
    }
}
