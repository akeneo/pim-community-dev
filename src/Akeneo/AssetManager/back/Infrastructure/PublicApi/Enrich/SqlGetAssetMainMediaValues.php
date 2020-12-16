<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\PublicApi\Enrich;

use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
final class SqlGetAssetMainMediaValues implements GetAssetMainMediaValuesInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritDoc}
     */
    public function forAssetFamilyAndAssetCodes(string $assetFamilyIdentifier, array $assetCodes): array
    {
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
WHERE asset.asset_family_identifier = :assetFamilyIdentifier AND asset.code IN (:assetCodes)
SQL;

        $rawResults = $this->connection->executeQuery(
            $sql,
            ['assetFamilyIdentifier' => $assetFamilyIdentifier, 'assetCodes' => $assetCodes],
            ['assetCodes' => Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        $results = [];
        foreach ($rawResults as $rawResult) {
            $results[$rawResult['code']] = $this->filterMainMediaValues(
                json_decode($rawResult['value_collection'], true),
                $rawResult['attribute_as_main_media']
            );
        }

        return $results;
    }

    private function filterMainMediaValues(array $rawValueCollection, string $attributeAsMainMediaIdentifier): array
    {
        return array_values(array_filter(
            $rawValueCollection,
            fn (array $value) => $value['attribute'] === $attributeAsMainMediaIdentifier
        ));
    }
}
