<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Asset\AssetDetails;
use Akeneo\AssetManager\Domain\Query\Attribute\FindValueKeysByAttributeTypeInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class AssetDetailsHydrator implements AssetDetailsHydratorInterface
{
    private AbstractPlatform $platform;

    private FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType;

    private ValueHydratorInterface $valueHydrator;

    public function __construct(
        Connection $connection,
        FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType,
        ValueHydratorInterface $valueHydrator
    ) {
        $this->platform = $connection->getDatabasePlatform();
        $this->findValueKeysByAttributeType = $findValueKeysByAttributeType;
        $this->valueHydrator = $valueHydrator;
    }

    public function hydrate(
        array $row,
        array $emptyValues,
        ValueKeyCollection $valueKeyCollection,
        array $attributes
    ): AssetDetails {
        $attributeAsLabel = Type::getType(Type::STRING)->convertToPHPValue($row['attribute_as_label'], $this->platform);
        $attributeAsMainMedia = Type::getType(Type::STRING)->convertToPHPValue($row['attribute_as_main_media'], $this->platform);
        $valueCollection = Type::getType(Type::JSON_ARRAY)->convertToPHPValue($row['value_collection'], $this->platform);
        $assetIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($row['identifier'], $this->platform);
        $assetFamilyIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($row['asset_family_identifier'], $this->platform);
        $assetCode = Type::getType(Type::STRING)
            ->convertToPHPValue($row['code'], $this->platform);
        $createdAt = Type::getType(Types::DATETIME_IMMUTABLE)
            ->convertToPHPValue($row['created_at'], $this->platform);
        $updatedAt = Type::getType(Types::DATETIME_IMMUTABLE)
            ->convertToPHPValue($row['updated_at'], $this->platform);
        $values = $this->hydrateValues($valueKeyCollection, $attributes, $valueCollection);
        $normalizedValues = [];
        foreach ($values as $key => $value) {
            $normalizedValues[$key] = $value->normalize();
        }

        $allValues = $this->createEmptyValues($emptyValues, $normalizedValues);

        $labels = $this->getLabelsFromValues($valueCollection, $attributeAsLabel);
        $assetImage = $this->getImage($valueCollection, $attributes[$attributeAsMainMedia]);

        return new AssetDetails(
            AssetIdentifier::fromString($assetIdentifier),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AttributeIdentifier::fromString($attributeAsMainMedia),
            AssetCode::fromString($assetCode),
            LabelCollection::fromArray($labels),
            $createdAt,
            $updatedAt,
            $assetImage,
            $allValues,
            true
        );
    }

    private function createEmptyValues(array $emptyValues, array $valueCollection): array
    {
        $result = [];
        foreach ($emptyValues as $key => $value) {
            if (array_key_exists($key, $valueCollection)) {
                $value['data'] = $valueCollection[$key]['data'];
            }

            $result[] = $value;
        }

        return $result;
    }

    private function getLabelsFromValues($valueCollection, $attributeAsLabel): array
    {
        return array_reduce(
            $valueCollection,
            function (array $labels, array $value) use ($attributeAsLabel) {
                if ($value['attribute'] === $attributeAsLabel) {
                    $localeCode = $value['locale'];
                    $label = (string) $value['data'];
                    $labels[$localeCode] = $label;
                }

                return $labels;
            },
            []
        );
    }

    private function hydrateValues(ValueKeyCollection $valueKeyCollection, array $attributes, $valueCollection): array
    {
        $hydratedValues = [];
        foreach ($valueKeyCollection as $valueKey) {
            $key = (string) $valueKey;
            if (!array_key_exists($key, $valueCollection)) {
                continue;
            }

            $value = $valueCollection[$key];
            $attributeIdentifier = $value['attribute'];
            $value = $this->valueHydrator->hydrate($value, $attributes[$attributeIdentifier]);
            if ($value->isEmpty()) {
                continue;
            }
            $hydratedValues[$key] = $value;
        }

        return $hydratedValues;
    }

    private function getImage(array $valueCollection, AbstractAttribute $attributeAsMainMedia): array
    {
        $imageValues = array_filter(
            $valueCollection,
            fn(array $value) => $value['attribute'] === $attributeAsMainMedia->getIdentifier()->normalize()
        );

        $result = array_map(function (array $value) use ($attributeAsMainMedia) {
            $value['attribute'] = $attributeAsMainMedia->normalize();
            return $value;
        }, $imageValues);

        return array_values($result);
    }
}
