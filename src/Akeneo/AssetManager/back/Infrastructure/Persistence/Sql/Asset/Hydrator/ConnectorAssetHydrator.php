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
use Akeneo\AssetManager\Domain\Query\Asset\Connector\ConnectorAsset;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\Transformer\ConnectorValueTransformerRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Webmozart\Assert\Assert;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ConnectorAssetHydrator
{
    private AbstractPlatform $platform;

    private ConnectorValueTransformerRegistry $valueTransformerRegistry;

    public function __construct(
        Connection $connection,
        ConnectorValueTransformerRegistry $valueTransformerRegistry
    ) {
        $this->platform = $connection->getDatabasePlatform();
        $this->valueTransformerRegistry = $valueTransformerRegistry;
    }

    public function hydrate(array $row, ValueKeyCollection $valueKeyCollection, array $attributes): ConnectorAsset
    {
        $valueCollection = Type::getType(Type::JSON_ARRAY)
            ->convertToPHPValue($row['value_collection'], $this->platform);
        $assetCode = Type::getType(Type::STRING)
            ->convertToPHPValue($row['code'], $this->platform);

        $filteredRawValues = [];
        foreach ($valueKeyCollection as $valueKey) {
            $key = (string) $valueKey;
            if (!array_key_exists($key, $valueCollection)) {
                continue;
            }

            $filteredRawValues[$key] = $valueCollection[$key];
        }

        $normalizedValues = $this->normalizeValues($filteredRawValues, $attributes);

        return new ConnectorAsset(AssetCode::fromString($assetCode), $normalizedValues);
    }

    private function normalizeValues(array $rawValues, array $attributes): array
    {
        $normalizedValues = [];

        foreach ($rawValues as $key => $rawValue) {
            $attributeIdentifier = $rawValue['attribute'];
            Assert::notNull($attributes[$attributeIdentifier] ?? null, sprintf(
                'Attribute not found for the identifier %s', $attributeIdentifier
            ));

            $attribute = $attributes[$attributeIdentifier];
            $attributeCode = (string) $attribute->getCode();
            $valueTransformer = $this->valueTransformerRegistry->getTransformer($attribute);
            $normalizedValue = $valueTransformer->transform($rawValue, $attribute);

            if (null !== $normalizedValue) {
                $normalizedValues[$attributeCode][] = $normalizedValue;
            }
        }

        return $normalizedValues;
    }
}
