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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\ReferenceEntity\Domain\Query\Record\Connector\ConnectorRecord;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\Transformer\ConnectorValueTransformerRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Webmozart\Assert\Assert;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ConnectorRecordHydrator
{
    private AbstractPlatform $platform;

    public function __construct(
        Connection $connection,
        private ConnectorValueTransformerRegistry $valueTransformerRegistry
    ) {
        $this->platform = $connection->getDatabasePlatform();
    }

    public function hydrate(array $row, ValueKeyCollection $valueKeyCollection, array $attributes): ConnectorRecord
    {
        $valueCollection = Type::getType(Types::JSON)
            ->convertToPHPValue($row['value_collection'], $this->platform);
        $recordCode = Type::getType(Types::STRING)
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

        return new ConnectorRecord(
            RecordCode::fromString($recordCode),
            $normalizedValues,
            Type::getType(Types::DATETIME_IMMUTABLE)->convertToPHPValue($row['created_at'], $this->platform),
            Type::getType(Types::DATETIME_IMMUTABLE)->convertToPHPValue($row['updated_at'], $this->platform),
        );
    }

    private function normalizeValues(array $rawValues, array $attributes): array
    {
        $normalizedValues = [];

        foreach ($rawValues as $key => $rawValue) {
            $attributeIdentifier = $rawValue['attribute'];
            Assert::notNull($attributes[$attributeIdentifier] ?? null, sprintf(
                'Attribute not found for the identifier %s',
                $attributeIdentifier
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
