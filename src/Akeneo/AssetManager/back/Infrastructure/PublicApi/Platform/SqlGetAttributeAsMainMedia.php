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

class SqlGetAttributeAsMainMedia implements GetAttributeAsMainMediaInterface
{
    private array $attributeAsMainMediaTypes;
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->attributeAsMainMediaTypes = [];
        $this->connection = $connection;
    }

    public function forAssetFamilyCode(string $assetFamilyCode): AttributeAsMainMedia
    {
        if (array_key_exists($assetFamilyCode, $this->attributeAsMainMediaTypes)) {
            return $this->attributeAsMainMediaTypes[$assetFamilyCode];
        }

        $sql = <<<SQL
SELECT
    attribute.attribute_type, attribute.value_per_channel, attribute.value_per_locale
FROM akeneo_asset_manager_asset_family family
    JOIN akeneo_asset_manager_attribute attribute ON family.attribute_as_main_media = attribute.identifier
WHERE family.identifier = :assetFamilyIdentifier
SQL;

        $result = $this->connection->executeQuery(
            $sql,
            ['assetFamilyIdentifier' => $assetFamilyCode]
        )->fetch();

        if (empty($result)) {
            throw new \RuntimeException(sprintf('Asset family "%s" does not exist', $assetFamilyCode));
        }

        $this->attributeAsMainMediaTypes[$assetFamilyCode] = new AttributeAsMainMedia(
            $result['attribute_type'],
            (bool) $result['value_per_channel'],
            (bool) $result['value_per_locale']
        );

        return $this->attributeAsMainMediaTypes[$assetFamilyCode];
    }
}
