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

namespace Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record;

use Akeneo\ReferenceEntity\Domain\Event\RecordDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Event\RecordUpdatedEvent;
use Akeneo\ReferenceEntity\Domain\Event\ReferenceEntityRecordsDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindValueKeyCollectionInterface;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindValueKeysByAttributeTypeInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindIdentifiersByReferenceEntityAndCodesInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordNotFoundException;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Infrastructure\Persistence\Sql\Record\Hydrator\RecordHydratorInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlRecordRepository implements RecordRepositoryInterface
{
    /** @var Connection */
    private $sqlConnection;

    /** @var RecordHydratorInterface */
    private $recordHydrator;

    /** @var FindValueKeyCollectionInterface */
    private $findValueKeyCollection;

    /** @var FindAttributesIndexedByIdentifierInterface */
    private $findAttributesIndexedByIdentifier;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var FindIdentifiersByReferenceEntityAndCodesInterface */
    private $findIdentifiersByReferenceEntityAndCodes;

    /** @var FindValueKeysByAttributeTypeInterface */
    private $findValueKeysByAttributeType;

    public function __construct(
        Connection $sqlConnection,
        RecordHydratorInterface $recordHydrator,
        FindValueKeyCollectionInterface $findValueKeyCollection,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        EventDispatcherInterface $eventDispatcher,
        FindIdentifiersByReferenceEntityAndCodesInterface $findIdentifiersByReferenceEntityAndCodes,
        FindValueKeysByAttributeTypeInterface $findValueKeysByAttributeType
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->recordHydrator = $recordHydrator;
        $this->findValueKeyCollection = $findValueKeyCollection;
        $this->findAttributesIndexedByIdentifier = $findAttributesIndexedByIdentifier;
        $this->eventDispatcher = $eventDispatcher;
        $this->findIdentifiersByReferenceEntityAndCodes = $findIdentifiersByReferenceEntityAndCodes;
        $this->findValueKeysByAttributeType = $findValueKeysByAttributeType;
    }

    public function count(): int
    {
        $sql = 'SELECT COUNT(*) FROM akeneo_reference_entity_record';
        $statement = $this->sqlConnection->executeQuery($sql);
        $count = (int) $statement->fetchColumn();

        return $count;
    }

    public function create(Record $record): void
    {
        $valueCollection = $this->replaceCodesByIdentifiers(
            $record->getValues()->normalize(),
            $record->getReferenceEntityIdentifier()
        );

        $insert = <<<SQL
        INSERT INTO akeneo_reference_entity_record
            (identifier, code, reference_entity_identifier, value_collection)
        VALUES (:identifier, :code, :reference_entity_identifier, :value_collection);
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $insert,
            [
                'identifier' => (string) $record->getIdentifier(),
                'code' => (string) $record->getCode(),
                'reference_entity_identifier' => (string) $record->getReferenceEntityIdentifier(),
                'value_collection' => $valueCollection,
                'created_at' => $record->getCreatedAt(),
                'updated_at' => $record->getUpdatedAt(),
            ],
            [
                'value_collection' => Type::JSON_ARRAY,
                'created_at' => Types::DATETIME_IMMUTABLE,
                'updated_at' => Types::DATETIME_IMMUTABLE,
            ]
        );
        if ($affectedRows > 1) {
            throw new \RuntimeException(
                sprintf('Expected to create one record, but %d rows were affected', $affectedRows)
            );
        }

        $this->eventDispatcher->dispatch(
            new RecordUpdatedEvent(
                $record->getIdentifier(),
                $record->getCode(),
                $record->getReferenceEntityIdentifier()
            ),
            RecordUpdatedEvent::class
        );
    }

    public function update(Record $record): void
    {
        $valueCollection = $this->replaceCodesByIdentifiers(
            $record->getValues()->normalize(),
            $record->getReferenceEntityIdentifier()
        );

        $update = <<<SQL
        UPDATE akeneo_reference_entity_record
        SET value_collection = :value_collection, updated_at = :updated_at
        WHERE identifier = :identifier;
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $update,
            [
                'identifier' => $record->getIdentifier(),
                'value_collection' => $valueCollection,
                'updated_at' => $record->getUpdatedAt(),
            ],
            [
                'value_collection' => Type::JSON_ARRAY,
                'updated_at' => Types::DATETIME_IMMUTABLE
            ]
        );

        if ($affectedRows > 1) {
            throw new \RuntimeException(
                sprintf('Expected to update one record, but %d rows were affected', $affectedRows)
            );
        }

        $this->eventDispatcher->dispatch(
            new RecordUpdatedEvent(
                $record->getIdentifier(),
                $record->getCode(),
                $record->getReferenceEntityIdentifier()
            ),
            RecordUpdatedEvent::class
        );
    }

    public function getByReferenceEntityAndCode(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        RecordCode $code
    ): Record {
        $fetch = <<<SQL
        SELECT identifier, code, reference_entity_identifier, value_collection, created_at, updated_at
        FROM akeneo_reference_entity_record
        WHERE code = :code AND reference_entity_identifier = :reference_entity_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            [
                'code' => (string) $code,
                'reference_entity_identifier' => (string) $referenceEntityIdentifier,
            ]
        );
        $result = $statement->fetch();

        if (!$result) {
            throw RecordNotFoundException::withReferenceEntityAndCode($referenceEntityIdentifier, $code);
        }

        return $this->hydrateRecord($result);
    }

    public function getByIdentifier(RecordIdentifier $identifier): Record
    {
        $fetch = <<<SQL
        SELECT 
            record.identifier,
            record.code,
            record.reference_entity_identifier,
            record.value_collection,
            reference.attribute_as_label,
            reference.attribute_as_image,
            created_at,
            updated_at
        FROM akeneo_reference_entity_record AS record
        INNER JOIN akeneo_reference_entity_reference_entity AS reference
            ON reference.identifier = record.reference_entity_identifier
        WHERE record.identifier = :record_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            [
                'record_identifier' => (string) $identifier,
            ]
        );
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        if (!$result) {
            throw RecordNotFoundException::withIdentifier($identifier);
        }

        return $this->hydrateRecord($result);
    }

    public function deleteByReferenceEntity(
        ReferenceEntityIdentifier $referenceEntityIdentifier
    ): void {
        $sql = <<<SQL
        DELETE FROM akeneo_reference_entity_record
        WHERE reference_entity_identifier = :reference_entity_identifier;
SQL;
        $this->sqlConnection->executeUpdate(
            $sql,
            [
                'reference_entity_identifier' => (string) $referenceEntityIdentifier,
            ]
        );

        $this->eventDispatcher->dispatch(
            new ReferenceEntityRecordsDeletedEvent($referenceEntityIdentifier),
            ReferenceEntityRecordsDeletedEvent::class
        );
    }

    public function deleteByReferenceEntityAndCode(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        RecordCode $code
    ): void {
        $identifiers = $this->findIdentifiersByReferenceEntityAndCodes->find($referenceEntityIdentifier, [$code]);

        $sql = <<<SQL
        DELETE FROM akeneo_reference_entity_record
        WHERE code = :code AND reference_entity_identifier = :reference_entity_identifier;
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $sql,
            [
                'code' => (string) $code,
                'reference_entity_identifier' => (string) $referenceEntityIdentifier,
            ]
        );

        if (0 === $affectedRows) {
            throw new RecordNotFoundException();
        }

        $this->eventDispatcher->dispatch(
            new RecordDeletedEvent(
                $identifiers[$code->normalize()],
                $code,
                $referenceEntityIdentifier
            ),
            RecordDeletedEvent::class
        );
    }

    public function nextIdentifier(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        RecordCode $code
    ): RecordIdentifier {
        return RecordIdentifier::create(
            (string) $referenceEntityIdentifier,
            (string) $code,
            Uuid::uuid4()->toString()
        );
    }

    public function countByReferenceEntity(ReferenceEntityIdentifier $referenceEntityIdentifier): int
    {
        $fetch = <<<SQL
        SELECT COUNT(*)
        FROM akeneo_reference_entity_record
        WHERE reference_entity_identifier = :reference_entity_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            ['reference_entity_identifier' => $referenceEntityIdentifier,]
        );
        $count = $statement->fetchColumn();

        return intval($count);
    }

    private function getReferenceEntityIdentifier($result): ReferenceEntityIdentifier
    {
        if (!isset($result['reference_entity_identifier'])) {
            throw new \LogicException('The record should have a reference entity identifier');
        }
        $normalizedReferenceEntityIdentifier = Type::getType(Type::STRING)->convertToPHPValue(
            $result['reference_entity_identifier'],
            $this->sqlConnection->getDatabasePlatform()
        );

        return ReferenceEntityIdentifier::fromString($normalizedReferenceEntityIdentifier);
    }

    private function hydrateRecord($result): Record
    {
        $referenceEntityIdentifier = $this->getReferenceEntityIdentifier($result);
        $valueKeyCollection = $this->findValueKeyCollection->find($referenceEntityIdentifier);
        $attributesIndexedByIdentifier = $this->findAttributesIndexedByIdentifier->find($referenceEntityIdentifier);

        return $this->recordHydrator->hydrate(
            $result,
            $valueKeyCollection,
            $attributesIndexedByIdentifier
        );
    }

    private function replaceCodesByIdentifiers(
        array $valueCollection,
        ReferenceEntityIdentifier $referenceEntityIdentifier
    ): array {
        $recordsValueKeys = $this->findValueKeysByAttributeType->find(
            $referenceEntityIdentifier,
            ['record', 'record_collection']
        );

        if (empty($recordsValueKeys)) {
            return $valueCollection;
        }

        $onlyRecordsValues = array_intersect_key($valueCollection, array_flip($recordsValueKeys));

        if (empty($onlyRecordsValues)) {
            return $valueCollection;
        }

        $attributesIndexedByIdentifier = $this->findAttributesIndexedByIdentifier->find($referenceEntityIdentifier);

        // Replace codes by identifiers in the value collection
        foreach ($onlyRecordsValues as $valueKey => $value) {
            /** @var RecordAttribute|RecordCollectionAttribute $attribute */
            $attribute = $attributesIndexedByIdentifier[$value['attribute']];

            $indexedIdentifiers = $this->findIdentifiersByReferenceEntityAndCodes->find(
                $attribute->getRecordType(),
                is_array($value['data']) ? $value['data'] : [$value['data']]
            );

            if (is_array($value['data'])) {
                $value['data'] = array_map(function ($code) use ($indexedIdentifiers) {
                    return (string) $indexedIdentifiers[$code];
                }, $value['data']);
            } else {
                $value['data'] = (string) $indexedIdentifiers[$value['data']];
            }

            $valueCollection[$valueKey] = $value;
        }

        return $valueCollection;
    }
}
