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

use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Doctrine\DBAL\Connection;

class SqlGetAttributeAsMainMedia implements GetAttributeAsMainMediaInterface
{
    /** @var array<string, AttributeAsMainMedia> */
    private array $attributesAsMainMedia = [];

    public function __construct(private Connection $connection)
    {
    }

    public function forAssetFamilyCode(string $assetFamilyCode): AttributeAsMainMedia
    {
        if (array_key_exists($assetFamilyCode, $this->attributesAsMainMedia)) {
            return $this->attributesAsMainMedia[$assetFamilyCode];
        }

        $sql = <<<SQL
SELECT
    attribute.attribute_type, attribute.value_per_channel, attribute.value_per_locale, attribute.additional_properties
FROM akeneo_asset_manager_asset_family family
    JOIN akeneo_asset_manager_attribute attribute ON family.attribute_as_main_media = attribute.identifier
WHERE family.identifier = :assetFamilyIdentifier
SQL;

        $result = $this->connection->executeQuery(
            $sql,
            ['assetFamilyIdentifier' => $assetFamilyCode]
        )->fetchAssociative();

        if (empty($result)) {
            throw new \RuntimeException(sprintf('Asset family "%s" does not exist', $assetFamilyCode));
        }

        switch ($result['attribute_type']) {
            case MediaFileAttribute::ATTRIBUTE_TYPE:
                $this->attributesAsMainMedia[$assetFamilyCode] = new MediaFileAsMainMedia(
                    (bool) $result['value_per_channel'],
                    (bool) $result['value_per_locale'],
                );
                break;
            case MediaLinkAttribute::ATTRIBUTE_TYPE:
                $additionalProperties = json_decode($result['additional_properties'], true);
                $this->attributesAsMainMedia[$assetFamilyCode] = new MediaLinkAsMainMedia(
                    (bool) $result['value_per_channel'],
                    (bool) $result['value_per_locale'],
                    $additionalProperties['prefix'] ?? '',
                    $additionalProperties['suffix'] ?? '',
                );
                break;
            default:
                throw new \InvalidArgumentException('Unsupported attribute type as main media');
        }

        return $this->attributesAsMainMedia[$assetFamilyCode];
    }
}
