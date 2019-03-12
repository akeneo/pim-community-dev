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

use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class RecordHydrator implements RecordHydratorInterface
{
    /** @var ValueHydratorInterface */
    private $valueHydrator;

    /** @var AbstractPlatform */
    private $platform;

    public function __construct(Connection $connection, ValueHydratorInterface $valueHydrator)
    {
        $this->valueHydrator = $valueHydrator;
        $this->platform = $connection->getDatabasePlatform();
    }

    public function hydrate(
        array $row,
        ValueKeyCollection $valueKeyCollection,
        array $attributes
    ): Record {
        $recordIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($row['identifier'], $this->platform);
        $referenceEntityIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($row['reference_entity_identifier'], $this->platform);
        $recordCode = Type::getType(Type::STRING)
            ->convertToPHPValue($row['code'], $this->platform);
        $valueCollection = json_decode($row['value_collection'], true);

        $record = Record::create(
            RecordIdentifier::fromString($recordIdentifier),
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            RecordCode::fromString($recordCode),
            ValueCollection::fromValues($this->hydrateValues($valueKeyCollection, $attributes, $valueCollection))
        );

        return $record;
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
