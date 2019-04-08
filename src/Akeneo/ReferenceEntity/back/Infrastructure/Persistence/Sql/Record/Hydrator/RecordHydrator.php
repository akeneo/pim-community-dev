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
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindValueKeysByAttributeTypeInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKeyCollection;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindCodesByIdentifiersInterface;
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

    /** @var FindCodesByIdentifiersInterface */
    private $findCodesByIdentifiers;

    /** @var FindValueKeysByAttributeTypeInterface */
    private $findValueKeysByAttributeType;

    public function __construct(
        Connection $connection,
        ValueHydratorInterface $valueHydrator,
        FindCodesByIdentifiersInterface $findCodesByIdentifiers,
        FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType
    ) {
        $this->valueHydrator = $valueHydrator;
        $this->platform = $connection->getDatabasePlatform();
        $this->findCodesByIdentifiers = $findCodesByIdentifiers;
        $this->findValueKeysByAttributeType = $findValueKeysByAttributeType;
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
        $valueCollection = $this->replaceIdentifiersByCodes($valueCollection, $referenceEntityIdentifier);

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

    /**
     * TODO: If the front directly handles record identifier as data, then we can drop this method and its call
     */
    private function replaceIdentifiersByCodes(array $valueCollection, string $referenceEntityIdentifier): array
    {
        // Values keys for record/record collection values
        $recordsValueKeys = $this->findValueKeysByAttributeType->find(
            ReferenceEntityIdentifier::fromString($referenceEntityIdentifier),
            ['record', 'record_collection']
        );

        $onlyRecordsValues = array_intersect_key($valueCollection, array_flip($recordsValueKeys));

        if (empty($onlyRecordsValues)) {
            return $valueCollection;
        }

        // Get identifiers for which we have to retrieve the code
        $identifiers = [];
        foreach ($onlyRecordsValues as $value) {
            $data = is_array($value['data']) ? $value['data'] : [$value['data']];
            $identifiers = array_merge($identifiers, $data);
        }

        $identifiers = array_unique($identifiers);

        // Retrieve the codes
        $indexedCodes = $this->findCodesByIdentifiers->find($identifiers);

        // Replace identifiers by code in the value collection
        foreach ($onlyRecordsValues as $valueKey => $value) {
            if (is_array($value['data'])) {
                $value['data'] = array_map(function ($identifier) use ($indexedCodes) {
                    return $indexedCodes[$identifier];
                }, $value['data']);
            } else {
                $value['data'] = $indexedCodes[$value['data']];
            }

            $valueCollection[$valueKey] = $value;
        }

        return $valueCollection;
    }
}
