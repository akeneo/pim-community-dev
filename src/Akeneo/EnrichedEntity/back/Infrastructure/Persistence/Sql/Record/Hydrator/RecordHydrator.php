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

namespace Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Record\Hydrator;

use Akeneo\EnrichedEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\EnrichedEntity\Infrastructure\Persistence\Sql\Record\Hydrator\ValueHydratorInterface;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordCode;
use Akeneo\EnrichedEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\ValueCollection;
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
        $labels = json_decode($row['labels'], true);
        $valueCollection = json_decode($row['value_collection'], true);
        $recordIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($row['identifier'], $this->platform);
        $enrichedEntityIdentifier = Type::getType(Type::STRING)
            ->convertToPHPValue($row['enriched_entity_identifier'], $this->platform);
        $recordCode = Type::getType(Type::STRING)
            ->convertToPHPValue($row['code'], $this->platform);

        $hydratedValues = [];
        foreach ($valueKeyCollection->normalize() as $key) {
            if (!array_key_exists($key, $valueCollection)) {
                continue;
            }

            $rawValue = $valueCollection[$key];
            $attributeIdentifier = $rawValue['attribute'];

            $hydratedValues[$key] = $this->valueHydrator->hydrate(
                $rawValue,
                $attributes[$attributeIdentifier]
            );
        }

        $record = Record::create(
            RecordIdentifier::fromString($recordIdentifier),
            EnrichedEntityIdentifier::fromString($enrichedEntityIdentifier),
            RecordCode::fromString($recordCode),
            $labels,
            ValueCollection::fromValues($hydratedValues)
        );

        return $record;
    }
}
