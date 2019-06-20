<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Attribute\Hydrator;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ImageAttributeHydrator extends AbstractAttributeHydrator
{
    public function supports(array $row): bool
    {
        return isset($row['attribute_type']) && 'image' === $row['attribute_type'];
    }

    public function convertAdditionalProperties(AbstractPlatform $platform, array $row): array
    {
        $row['allowed_extensions'] = $row['additional_properties']['allowed_extensions'];
        $row['max_file_size'] = Type::getType(Type::STRING)->convertToPHPValue($row['additional_properties']['max_file_size'], $platform);

        return $row;
    }

    public function hydrateAttribute(array $row): AbstractAttribute
    {
        $maxFileSize = null === $row['max_file_size'] ?
            AttributeMaxFileSize::noLimit()
            : AttributeMaxFileSize::fromString($row['max_file_size']);

        return ImageAttribute::create(
            AttributeIdentifier::fromString($row['identifier']),
            AssetFamilyIdentifier::fromString($row['asset_family_identifier']),
            AttributeCode::fromString($row['code']),
            LabelCollection::fromArray($row['labels']),
            AttributeOrder::fromInteger($row['attribute_order']),
            AttributeIsRequired::fromBoolean($row['is_required']),
            AttributeValuePerChannel::fromBoolean($row['value_per_channel']),
            AttributeValuePerLocale::fromBoolean($row['value_per_locale']),
            $maxFileSize,
            AttributeAllowedExtensions::fromList($row['allowed_extensions'])
        );
    }

    protected function getExpectedProperties(): array
    {
        return [
            'identifier',
            'asset_family_identifier',
            'code',
            'labels',
            'attribute_order',
            'is_required',
            'value_per_locale',
            'value_per_channel',
            'attribute_type',
            'max_file_size',
            'allowed_extensions',
        ];
    }
}
