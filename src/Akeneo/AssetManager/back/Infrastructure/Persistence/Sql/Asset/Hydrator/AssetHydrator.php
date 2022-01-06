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

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKeyCollection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class AssetHydrator implements AssetHydratorInterface
{
    public function __construct(
        private Connection $connection,
        private ValueHydratorInterface $valueHydrator
    ) {
    }

    public function hydrate(
        array $row,
        ValueKeyCollection $valueKeyCollection,
        array $attributes
    ): Asset {
        $platform = $this->connection->getDatabasePlatform();

        $assetIdentifier = Type::getType(Types::STRING)
            ->convertToPHPValue($row['identifier'], $platform);
        $assetFamilyIdentifier = Type::getType(Types::STRING)
            ->convertToPHPValue($row['asset_family_identifier'], $platform);
        $assetCode = Type::getType(Types::STRING)
            ->convertToPHPValue($row['code'], $platform);
        $valueCollection = json_decode($row['value_collection'], true);
        $createdAt = Type::getType(Types::DATETIME_IMMUTABLE)->convertToPHPValue($row['created_at'], $platform);
        $updatedAt = Type::getType(Types::DATETIME_IMMUTABLE)->convertToPHPValue($row['updated_at'], $platform);

        return Asset::fromState(
            AssetIdentifier::fromString($assetIdentifier),
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AssetCode::fromString($assetCode),
            ValueCollection::fromValues($this->hydrateValues($valueKeyCollection, $attributes, $valueCollection)),
            $createdAt,
            $updatedAt
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

            $rawValue = $valueCollection[$key];
            $attributeIdentifier = $rawValue['attribute'];
            $value = $this->valueHydrator->hydrate($rawValue, $attributes[$attributeIdentifier]);
            if ($value->isEmpty()) {
                continue;
            }
            $hydratedValues[$key] = $value;
        }

        return $hydratedValues;
    }
}
