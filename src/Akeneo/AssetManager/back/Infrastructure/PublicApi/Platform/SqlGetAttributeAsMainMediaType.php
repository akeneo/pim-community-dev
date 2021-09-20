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

class SqlGetAttributeAsMainMediaType implements GetAttributeAsMainMediaTypeInterface
{
    private array $attributeAsMainMediaTypes;
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->attributeAsMainMediaTypes = [];
        $this->connection = $connection;
    }

    public function isMediaFile(string $assetFamilyIdentifier): bool
    {
        return $this->isType($assetFamilyIdentifier, 'media_file');
    }

    public function isMediaLink(string $assetFamilyIdentifier): bool
    {
        return $this->isType($assetFamilyIdentifier, 'media_link');
    }

    private function forAssetFamilyIdentifier(string $assetFamilyIdentifier): ?string
    {
        if (array_key_exists($assetFamilyIdentifier, $this->attributeAsMainMediaTypes)) {
            return $this->attributeAsMainMediaTypes[$assetFamilyIdentifier];
        }

        $sql = <<<SQL
SELECT
    attribute.attribute_type
FROM akeneo_asset_manager_asset_family family
    JOIN akeneo_asset_manager_attribute attribute ON family.attribute_as_main_media = attribute.identifier
WHERE family.identifier = :assetFamilyIdentifier
SQL;

        $result = $this->connection->executeQuery(
            $sql,
            ['assetFamilyIdentifier' => $assetFamilyIdentifier]
        )->fetch();

        if (empty($result)) {
            throw new \RuntimeException(sprintf('Asset family "%s" does not exists', $assetFamilyIdentifier));
        }

        $attributeType = $result['attribute_type'];

        $this->attributeAsMainMediaTypes[$assetFamilyIdentifier] = $attributeType;

        return $attributeType;
    }

    private function isType(string $assetFamilyIdentifier, string $expectedType): bool
    {
        $attributeType = $this->forAssetFamilyIdentifier($assetFamilyIdentifier);
        return $expectedType === $attributeType;
    }
}
