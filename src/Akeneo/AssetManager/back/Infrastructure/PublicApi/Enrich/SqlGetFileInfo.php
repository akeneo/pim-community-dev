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

use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 */
final class SqlGetFileInfo implements GetFileInfoInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function forAssetFamilyAndAssetCodes(
        string $assetFamilyIdentifier,
        array $assetCodes,
        ?string $channelReference,
        ?string $localeReference
    ): array {
        if (0 === count($assetCodes)) {
            return [];
        }

        Assert::allString($assetCodes);

        $valueKey = $this->getMainMediaValueKey($assetFamilyIdentifier, $channelReference, $localeReference);

        if (null === $valueKey) {
            return [];
        }

        $sql = <<<SQL
SELECT JSON_UNQUOTE(JSON_EXTRACT(asset.value_collection, '$."%s".data.filePath')) as filePath,
       JSON_UNQUOTE(JSON_EXTRACT(asset.value_collection, '$."%s".data.originalFilename')) as originalFilename
FROM akeneo_asset_manager_asset asset
WHERE  asset.asset_family_identifier = :assetFamilyIdentifier AND asset.code IN (:assetCodes) 
SQL;

        $rawResults = $this->connection->executeQuery(
            sprintf($sql, $valueKey->__toString(), $valueKey->__toString()),
            ['assetFamilyIdentifier' => $assetFamilyIdentifier, 'assetCodes' => $assetCodes],
            ['assetCodes' => Connection::PARAM_STR_ARRAY]
        )->fetchAll();

        return $rawResults;
    }

    private function getMainMediaValueKey(
        string $assetFamilyIdentifier,
        ?string $channelReference,
        ?string $localeReference
    ): ?ValueKey {
        $sqlGetAttributeAsMainMediaIdentifier = <<<SQL
SELECT asset_attribute.identifier as attribute_identifier
FROM akeneo_asset_manager_asset_family family
JOIN akeneo_asset_manager_attribute asset_attribute ON family.attribute_as_main_media = asset_attribute.identifier
WHERE family.identifier = :assetFamilyIdentifier
AND asset_attribute.attribute_type = 'media_file'
SQL;

        $attributeIdentifier = $this->connection->executeQuery(
            $sqlGetAttributeAsMainMediaIdentifier,
            ['assetFamilyIdentifier' => $assetFamilyIdentifier]
        )->fetchColumn();

        if (null === $attributeIdentifier) {
            return null;
        }

        return ValueKey::create(
            AttributeIdentifier::fromString($attributeIdentifier),
            ChannelReference::createFromNormalized($channelReference),
            LocaleReference::createFromNormalized($localeReference)
        );
    }
}
