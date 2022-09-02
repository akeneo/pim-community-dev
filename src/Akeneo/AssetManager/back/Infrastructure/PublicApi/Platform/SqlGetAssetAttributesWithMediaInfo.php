<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\PublicApi\Platform;

use Doctrine\DBAL\Connection;

class SqlGetAssetAttributesWithMediaInfo implements SqlGetAssetAttributesWithMediaInfoInterface
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @inheritDoc
     */
    public function forFamilyIdentifiers(array $assetFamilyIdentifiers): array
    {
        $sql = <<<SQL
SELECT asset_family.identifier, asset_family.attribute_as_main_media, asset_attribute.attribute_type, JSON_UNQUOTE(additional_properties -> '$.media_type') as media_type
FROM akeneo_asset_manager_asset_family as asset_family
JOIN akeneo_asset_manager_attribute asset_attribute on asset_family.attribute_as_main_media = asset_attribute.identifier
WHERE asset_family.identifier IN (:assetFamilyIdentifiers)
SQL;
        return $this->connection
            ->executeQuery(
                $sql,
                ['assetFamilyIdentifiers' => $assetFamilyIdentifiers],
                ['assetFamilyIdentifiers' => Connection::PARAM_STR_ARRAY]
            )
            ->fetchAllAssociative();
    }
}
